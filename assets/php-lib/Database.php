<?php

namespace Lib;

use Exception;
use InvalidArgumentException;
use PDO;
use PDOException;

/**
 *  Database class provides the basic methods to access the database.
 */
class Database
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
     * @var PDO The database connection
     */
    private $_connection;

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
     * Database constructor creates the database instance using the dynamic configurations file.
     * If the file does not contain the database configuration, it will try to use the static (default)
     * database as fallback.
     */
    public function __construct()
    {
        $config = new Config();
        $this->_host = $config->get('database')['host'];
        $this->_database = $config->get('database')['name'];
        $this->_username = $config->get('database')['user'];
        $this->_password = $config->get('database')['password'];

        try {
            $this->_connection = new PDO("mysql:host=$this->_host;dbname=$this->_database;charset=utf8mb4", $this->_username, $this->_password);
        } catch (PDOException $e) {
            echo 'Connection failed: '.$e->getMessage();
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
    public static function resetDatabase()
    {
        $db = new self();

        $db->dropDatabase();
        $db->createDatabase();

        $db = new self();

        // TODO: remove hard-coded vars.
        $sql_folder = __DIR__.'/../../installation/';

        $db->executeFile($sql_folder.'base_schema.sql');
        $db->executeFile($sql_folder.'themes.sql');
    }

    /**
     * Drops a database the database. Removes the default db if none specified.
     *
     * @param string $db database name to drop
     *
     * @return array|bool result
     */
    public function dropDatabase($db = '')
    {
        if ($db == '') {
            $db = $this->_database;
        }

        return $this->rawQuery("DROP DATABASE $db");
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
            $stmt = $this->getConnection()->prepare($query);
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
     * @return PDO The database connection.
     */
    public function getConnection()
    {
        return $this->_connection;
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

        return $this->rawQuery("CREATE DATABASE $db");
    }

    /**
     * Runs a .sql file.
     *
     * @param $file string the sql file to run
     *
     * @throws Exception if the file is absent
     *
     * @return array|bool query result
     */
    public function executeFile($file)
    {
        if (empty($file)) {
            throw new Exception('No filename specified');
        }
        if (!file_exists($file)) {
            throw new Exception("File '$file' not found");
        }
        $sql = file_get_contents($file);

        return $this->rawQuery($sql);
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

        $stmt = $this->_connection;
        $stmt = $stmt->prepare($sql);

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
        return $this->_connection->lastInsertId();
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

        $stmt = $this->_connection->prepare($sql);
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

        $stmt = $this->_connection;
        $stmt = $stmt->prepare($sql);

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

        $stmt = $this->_connection;
        $stmt = $stmt->prepare($sql);

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

        $stmt = $this->_connection->prepare($sql);

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
            $stmt = $this->getConnection()->prepare($sql);

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
                $stmt = $this->getConnection()->prepare($sql);

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
