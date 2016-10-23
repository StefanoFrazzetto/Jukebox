<?php

header('Content-Type: application/json');

require '../../../php-lib/Wifi.php';

echo json_encode((new Wifi())->wifiScan());