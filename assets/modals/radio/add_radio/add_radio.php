<?php

header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../../../php-lib/Radio.php';

$url = $_GET['url'];
$name = $_GET['name'];

$radio = new Radio($name, $url);

$result = $radio->save();

if (!$result) {
    echo "{\"status\": \"error\"}";
} else {

    echo "{\"status\": \"success\"}";
}
