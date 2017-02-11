<?php

require_once '../../../../vendor/autoload.php';

use Lib\Wifi;

header('Content-Type: application/json');

$request_body = file_get_contents('php://input');

$network = json_decode($request_body, true);

(new Wifi())->updateNetwork($network);