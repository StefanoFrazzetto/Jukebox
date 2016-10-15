<?php

header('Content-Type: application/json');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$name = filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING);

$url = filter_input(INPUT_GET, 'url', FILTER_VALIDATE_URL);

function error($message)
{
    echo json_encode(["status" => "error", "message" => $message]);
    exit;
}

function success()
{
    echo '{"status":"success"}';
    exit;
}

if ($id == null) {
    error("Invalid ID provided");
}

if ($name == null) {
    error("Invalid Name provided");
}

if ($url == null) {
    error("Invalid URL provided");
}

require "../../../php-lib/Radio.php";

$radio = Radio::loadRadio($id);

if ($radio == null) {
    error("Radio not found");
}

$radio->setName($name);
$radio->setUrl($url);

if ($radio->save())
    success();
else
    error("Database error");