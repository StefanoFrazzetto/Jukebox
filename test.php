<?php

require_once "assets/php-lib/Network.php";

//header('Content-Type: text/json');

$class = new Network();

print_r($class->isConnected());