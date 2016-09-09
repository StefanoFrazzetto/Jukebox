<?php
$output = $_POST;

$json = json_encode($output);

print_r($json);

file_put_contents("../config/network_settings.json", $json);

require(__DIR__."/set_network.php");