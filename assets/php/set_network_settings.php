<?php

ini_set("error_log", __DIR__ . "/../../logs/set-network-errors.log");

$output = $_POST;

$json = json_encode($output);

file_put_contents("../config/network_settings.json", $json);

require(__DIR__."/set_network.php");