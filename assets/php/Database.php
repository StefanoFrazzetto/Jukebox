<?php

/**
*	Mysql database class
*/
class Database {
	private $_connection;
	private static $_instance; //The single instance

	private $_host = "localhost";
	private $_username = "root";
	private $_password = "password1000";
	private $_database = "zwytytws_albums";

	public static $_table_albums = "albums";
	public static $_table_radio_stations = "radio_stations";

	/*
	*	Get an instance of the Database
	*	@return Instance
	*/
	public static function getInstance() {
		if(!self::$_instance) { // If no instance then make one
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	// Constructor
	public function __construct() {
		try {
			$this->_connection = new PDO("mysql:host=$this->_host;dbname=$this->_database;charset=utf8mb4", $this->_username, $this->_password);
		} catch (PDOException $e) {
			echo 'Connection failed: ' . $e->getMessage();
		}
	}

	// Magic method clone is empty to prevent duplication of connection
	private function __clone() { 
	}

	// Get connection
	public function getConnection() {
		return $this->_connection;
	}

	/**
	*	Execute a raw query on the database.
	*	
	*	@param $query The query to be executed.
	*/
	public function rawQuery($query = "") {
		if($query == "") {
			return false;
		}

		try {
			$stmt = $this->getConnection()->prepare($sql);
			return $stmt->execute();
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	*	Add an album to `albums`
	*
	*	@param $table = "The table where you want to insert the album";
	*	@param $array = ['field1' => 'field1_val', 'field2' => 'field2_val'];
	*	@return TRUE if success, FALSE on error.
	*/
	public function insert($table, $array) {
		$array_fields = array_keys($array);
		$array_values = array_values($array);

		$fields = '(' . implode(',', $array_fields) . ')';
		$val_holders = '(:' . implode(', :', $array_fields) . ')';

		$sql = "INSERT INTO $table";
		$sql .= $fields . ' VALUES ' . $val_holders;

		$stmt = $this->_connection;
		$stmt = $stmt->prepare($sql);
		
		foreach ($array as $key => $value)
		    $stmt->bindValue(":$key", $value);

		return $stmt->execute();
	}

	/**
	*	Select $columns from $table with additional $query.
	*
	*	@param $columns = "The columns you want to select";
	*	@param $table = "The table to select from";
	*	@param $query = "Additional query WHERE, LIKE, etc.";
	*
	*	@return $rows = "Array of rows as OBJECT";
	*/
	public function select($columns = "*", $table = "albums", $query = "WHERE 1") {
		if(is_array($columns)) {	
			$columns = implode(', ', $columns);
		}

		$sql = "SELECT $columns FROM `$table`";
		$sql .= $query;

		$stmt = $this->_connection->prepare($sql);
		
		// DEBUG
		// $stmt->debugDumpParams();

		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_CLASS);
		$row_count = $stmt->rowCount();

		if($row_count == 0) {
			return null;
		}

		return $rows;
	}

	/**
	*	Update one or more records from $table passing
	*	$array as "column" => "value".
	*
	*	@param $table The table where to update the record(s)
	*	@param $array Associative array "key" => "value"
	*	@param $where Additional query: WHERE `column` = x LIKE "pattern"
	*/
	public function update($table = "", $array = "", $where = "") {
		if($table == "" || $array == "") {
			return false;
		}

		$sql = "UPDATE $table SET ";

		foreach ($array as $key => $value) {
			$sql .= $key . "=" . "'$value'" . ",";
		}

		$sql = rtrim($sql, ",");

		$sql .= " WHERE " . $where;

		$stmt = $this->_connection;
		$stmt = $stmt->prepare($sql);
		
		foreach ($array as $key => $value)
		    $stmt->bindValue(":$key", $value);

		// // DEBUG
		// $stmt->debugDumpParams();

		return $stmt->execute();
	}


	/**
	*	Removes record(s) from the table. 
	*
	*
	*/
	public function delete($table = "", $where = "") {
		if($table == "" || $where == "") {
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
	*	Drop $tables.
	*
	*	@return TRUE on success or FALSE on failure.
	*/
	public function drop($tables = "") {

		switch($tables) {
			case "albums":
				$sql = "TRUNCATE TABLE self::$_table_albums";
				break;
			case "radio_stations":
				$sql = "TRUNCATE TABLE self::$_table_radio_stations";
				break;
			default:
				$sql = "TRUNCATE TABLE self::$_table_albums; TRUNCATE TABLE self::$_table_radio_stations";
		}
		
		try {
			$stmt = $this->getConnection()->prepare($sql);
			return $stmt->execute();
		} catch (PDOException $e) {
			return false;
		}
	}

}