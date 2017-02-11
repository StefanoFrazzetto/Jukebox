<?php

require_once '../../vendor/autoload.php';

use Lib\Network;

$network = new Network();

$network->load_network();

try {
    $network->connect();
    echo "success";
} catch (Exception $e) {
    error_log("Failed to connect to the network. Cause: $e");
    echo "<pre>";
    echo $e;
    print_r($e->getTraceAsString());
    print_r($network);
    echo "</pre>";
}
