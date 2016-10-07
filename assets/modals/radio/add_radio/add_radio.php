<?php

header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../../php-lib/dbconnect.php';

$url = $_GET['url'];
$name = $_GET['name'];

$query = "INSERT INTO radio_stations (name, url) VALUES ('$name', '$url')";

$result = $mysqli->query($query);

if (!$result) {
    echo "{\"status\": \"error\"}";
} else {

    echo "{\"status\": \"success\"}";
}
