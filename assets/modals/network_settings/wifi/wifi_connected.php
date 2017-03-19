<?php

header('Content-Type: application/json');

require_once '../../../../vendor/autoload.php';

use Lib\Wifi;

echo json_encode((new Wifi())->getConnectedNetwork());
