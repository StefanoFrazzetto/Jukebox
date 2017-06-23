<?php

namespace Lib;

use Exception;
use InvalidArgumentException;
use PDO;
use PDOException;

/**
 * Database class provides the basic methods to access the database.
 *
 * @author Stefano Frazzetto
 *
 * @version 1.4.0
 */
class Database extends PDO
{
    /**
     * @var string The default table containing the albums
     */
    public static $_table_albums = 'albums';
    /**
     * @var string The default table containing the radio station
     */
    public static $_table_radio_stations = 'radio_stations';
    /**
     * @var string The default table containing the artists
     */
    public static $_table_artist = 'artists';
    /**
     * @var string The default table containing the songs
     */
    public static $_table_songs = 'songs';
    /**
     * @var string The default table containing the artists for each song
     */
    public static $_table_song_artists = 'song_artists';
    /**
     * @var Database The single database instance
     */
    private static $_instance;
    /**
     * @var string The database host
     */
    private $_host;
    /**
     * @var string The database name
     */
    private $_database;
    /**
     * @var string The database username
     */
    private $_username;
    /**
     * @var string The database password
     */
    private $_password;

    /**
     * @var string The path to the installation dir
     */
    private $_installation_dir_path;

    /**
     * Database constructor creates the database instance using the dynamic configurations file.
     * If the file does not contain the database configuration, it will try to use the static (default)
     * database.
     *
     * @param bool $use_default if set to false, no database will be selected (USE database).
     */
    public function __construct($use_default = true)
    {
        $config = new Config();
        if (!getenv('TRAVIS')) { // If env is not Travis-CI
            $this->_host = $config->get('database')['host'];
            $this->_database = $config->get('database')['name'];
            $this->_username = $config->get('database')['user'];
            $this->_password = $config->get('database')['password'];
        } else { // Otherwise use Travis config
            $this->setTravisConfig();
        }

        $this->_installation_dir_path = __DIR__.'/../../installation/';

        $this->__init($use_default);
    }

    /**
     * Set the database variables for Travis-CI environment.
     */
    private function setTravisConfig()
    {
        $this->_host = '127.0.0.1';
        $this->_database = 'test';
        $this->_username = 'travis';
        $this->_password = '';
    }

    /**
     * Initialize the database connection.
     *
     * If the database does not exist, it is created and then used.
     *
     * @param bool $use_default if set to false, no database will be selected (USE database).
     */
    private function __init($use_default)
    {
        try {
            parent::__construct("mysql:host=$this->_host;charset=utf8mb4", $this->_username, $this->_password);
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if (!$this->databaseExists($this->_database)) {
                $this->createDatabase();
            }

            if ($use_default) {
                $this->query("USE $this->_database");
            }
        } catch (PDOException $e) {
            error_log('Connection failed: '.$e->getMessage());
        }
    }

    /**
     * Return true if the database exists, false otherwise.
     *
     * @param string $db_name the database name
     *
     * @return bool true if the database exists, false otherwise
     */
    private function databaseExists($db_name)
    {
        $stmt = $this->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :DB_NAME');
        $stmt->execute(['DB_NAME' => $db_name]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Crates a new database. If no database name is specified, the default database will be created.
     *
     * @param string $db the name of the database to create
     *
     * @return array|bool
     */
    public function createDatabase($db = '')
    {
        if ($db == '') {
            $db = $this->_database;
        }

        $res_create_db = $this->query("CREATE DATABASE IF NOT EXISTS $db");
        $this->query("USE $db");
        $res_create_schema = $this->createSchema();

        return $res_create_db && $res_create_schema;
    }

    /**
     * Create the database schema using the SQL files in the installation dir.
     */
    private function createSchema()
    {
        $sql_folder = $this->_installation_dir_path;
        $this->executeFile($sql_folder.'base_schema.sql');
        $this->executeFile($sql_folder.'themes.sql');
    }

    /**
     * Runs a .sql file.
     *
     * @param $file string the sql file to run
     *
     * @throws Exception if the file name is not specified or does
     *                   not exist.
     *
     * @return array|bool query result
     */
    public function executeFile($file)
    {
        if (empty($file) || !file_exists($file)) {
            throw new Exception("File '$file' not found");
        }

        $sql = file_get_contents($file);

        return $this->rawQuery($sql);
    }

    /**
     * Execute a raw query on the database.
     *
     * @param string $query - the query to be executed
     *
     * @return array|bool An <b>array</b> containing the objects from the query or
     *                    <b>false</b> if the query was unsuccessful.
     */
    public function rawQuery($query)
    {
        if (!isset($query)) {
            return false;
        }

        try {
            $stmt = $this->prepare($query);
            $stmt->execute();

            if ($stmt === false) {
                return false;
            }

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * @return Database The current database instance. If there is no instance, a new one is returned instead.
     */
    public static function getInstance()
    {
        if (!self::$_instance) { // If no instance then make one
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Drops the main database and recreates it. WARNING: all data will be LOST.
     */
    public function resetDatabase()
    {
        $db = new self(false);

        $db->dropDatabase();
        $db->createDatabase();
    }

    /**
     * Drop a database.
     *
     * If no database is specified, the default one will be dropped.
     *
     * @param string $db the name of database to drop
     *
     * @return bool true if the database is dropped or does not
     *              exist, false otherwise.
     */
    public function dropDatabase($db = '')
    {
        if ($db == '') {
            $db = $this->_database;
        }

        return !$this->databaseExists($db) ?: $this->rawQuery("DROP DATABASE IF EXISTS $db");
    }

    /**
     * Run an insert query.
     *
     * @param $table
     * @param $array
     *
     * @return bool
     */
    public function insert($table, $array)
    {
        $array_fields = array_keys($array);

        $fields = '('.implode(',', $array_fields).')';
        $val_holders = '(:'.implode(', :', $array_fields).')';

        $sql = "INSERT INTO $table";
        $sql .= $fields.' VALUES '.$val_holders;

        $stmt = $this->prepare($sql);

        foreach ($array as $key => $value) {
            $value = addslashes($value);
            $stmt->bindValue(":$key", $value);
        }

        return $stmt->execute();
    }

    /**
     * Returns the last inserted ID.
     *
     * @return mixed
     */
    public function getLastInsertedID()
    {
        return $this->lastInsertId();
    }

    /**
     * Select $columns from $table with additional $query.
     *
     * @param string $columns The columns to select. Default is *.
     * @param string $table   The table where to perform the select from. Default is albums.
     * @param string $query   The additional query: WHERE ...
     *
     * @return array|null
     */
    public function select($columns = '*', $table = 'albums', $query = 'WHERE 1')
    {
        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }

        $sql = "SELECT $columns FROM $table ";
        $sql .= $query;

        $stmt = $this->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_CLASS);

        return $rows;
    }

    /**
     * Update a record.
     *
     * @param string $table
     * @param $array
     * @param string $where
     *
     * @return bool
     */
    public function update($table, $array, $where = '')
    {
        if ($table == '' || $array == null) {
            return false;
        }

        $sql = "UPDATE $table SET ";

        foreach ($array as $key => $value) {
            $value = addslashes($value);
            $sql .= $key.'='."'$value'".',';
        }

        $sql = rtrim($sql, ',');

        $sql .= ' WHERE '.$where;

        $stmt = $this->prepare($sql);

        foreach ($array as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        return $stmt->execute();
    }

    /**
     * Increase one or more numeric fields.
     *
     * @param $table string name of the table to update
     * @param $fields array | string field(s) to alter
     * @param $where string clause
     *
     * @return bool success status
     */
    public function increment($table, $fields, $where = '')
    {
        if ($table == '' || $fields == null) {
            return false;
        }

        $sql = "UPDATE $table SET ";

        if (!is_array($fields)) {
            $fields = [$fields];
        }

        foreach ($fields as $value) {
            $value = addslashes($value);
            $sql .= "$value = $value + 1,";
        }

        $sql = rtrim($sql, ',');

        if ($where != '') {
            $sql .= ' WHERE '.$where;
        }

        $stmt = $this->prepare($sql);

        return $stmt->execute();
    }

    /**
     * Counts the occurrences of a query in a table.
     *
     * @param $table
     * @param $where
     *
     * @return int the number of occurrences
     */
    public function count($table, $where)
    {
        $sql = "SELECT COUNT(*) FROM $table WHERE $where";

        $stmt = $this->prepare($sql);

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);
        $row_count = $stmt->rowCount();

        if ($row_count == 0) {
            return 0;
        }

        return intval($rows[0][0]);
    }

    /**
     * Remove record(s).
     *
     * @param string $table
     * @param string $where
     *
     * @return bool
     */
    public function delete($table, $where)
    {
        if ($table == null || $where == null) {
            return false;
        }

        $sql = "DELETE FROM $table WHERE ";
        $sql .= $where;

        try {
            $stmt = $this->prepare($sql);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Truncate one or more tables.
     *
     * @param string | string[] $tables the table to truncate or the array
     *
     * @return bool
     */
    public function truncate($tables)
    {
        if (empty($tables)) {
            throw new InvalidArgumentException('No table specified');
        }
        if ($tables === 'all') {
            $tables = [self::$_table_albums, self::$_table_artist, self::$_table_songs, self::$_table_song_artists, self::$_table_radio_stations];
        }

        if (is_array($tables)) {
            foreach ($tables as $table) {
                if (!$this->truncate($table)) {
                    return false;
                }
            }

            return true;
        } elseif (is_string($tables)) {
            $sql = "TRUNCATE TABLE $tables";

            try {
                $stmt = $this->prepare($sql);

                return $stmt->execute();
            } catch (PDOException $e) {
                return false;
            }
        }

        throw new InvalidArgumentException('Either a string or an array of string must be provided as parameter');
    }

    /** Magic method clone is empty to prevent duplication of connection */
    private function __clone()
    {
    }
}
