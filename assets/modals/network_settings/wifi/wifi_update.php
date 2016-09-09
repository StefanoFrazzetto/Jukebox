<?php

require '../../../php-lib/wifi_utils.php'; 

header('Content-Type: application/json');

$request_body = file_get_contents('php://input');

$network = json_decode($request_body, true);

updateNetwork($network);