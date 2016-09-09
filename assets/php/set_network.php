<pre>
<?php

include __DIR__."/../php-lib/Network.php";

$network = new Network();

//$network->interface = 'ra0';
//
//$network->ssid = "SSID";
//
//$network->encryption = "open";
//
//$network->dhcp = true;

$network->load_network();

print_r($network);

try {
    $network->connect();
} catch (Exception $e) {
    echo "error!";
    print_r($e->getTraceAsString());
}
