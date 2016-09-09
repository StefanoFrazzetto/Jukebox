<?php

header('Content-Type: application/json');

require '../../../php-lib/wifi_utils.php';



echo json_encode(wifiScan());

//var_dump((getConnectedNetwork()));