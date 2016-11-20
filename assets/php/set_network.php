<pre>
<?php

include __DIR__ . "/../php-lib/Network.php";

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
