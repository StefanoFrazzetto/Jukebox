<?php

header('Content-Type: application/json');

require 'dbconnect.php';
require 'get_cover.php';

$in_title = filter_input(INPUT_GET, 'title', FILTER_SANITIZE_STRING);

$result = $mysqli->query("SELECT title, id FROM $albums WHERE title LIKE '$in_title'");

if($result->num_rows){
    $json;
    $result = $result->fetch_assoc();
    $json->title = $result['title'];
    $json->cover_url = get_cover($result['id']);
    
    echo json_encode($json);
}else{
    echo '[0]';
}
