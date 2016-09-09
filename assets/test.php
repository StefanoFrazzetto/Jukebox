<?php

require_once "./php/Database.php";

$Database = new Database();

$array = array("title" => "Minchia", "artist" => "TESTAZZA");
$where = " `id` = 99999";

var_dump($Database->update($Database::$_table_albums, $array, $where));