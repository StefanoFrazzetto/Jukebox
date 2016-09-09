<?php

require 'dbconnect.php';
require 'get_cover.php';

$albumID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$result = $mysqli->query("SELECT title, artist FROM $albums WHERE id = $albumID LIMIT 1");

$results = $result->fetch_object();

header('Content-Type: application/json');

$json['title'] = $results->title;
$json['artist'] = $results->artist;
$json['cover'] = get_cover($albumID);
$json = json_encode($json);

echo $json;
