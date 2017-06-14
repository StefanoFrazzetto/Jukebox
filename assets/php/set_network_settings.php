<?php

require_once '../../vendor/autoload.php';

use Lib\Wifi;

ini_set('error_log', __DIR__.'/../../logs/set-network-errors.log');

$output = $_POST;

// If the current WiFi network is not saved, we need to save it, before attempting to connect
if ($output['network_type'] == 2) {
    $ssid = $output['ssid'];

    $wifi = new Wifi();

    $network = $wifi->getNetworkByEssid($ssid);

    if ($network == null) { // not stored in the WiFi database
        $wifi->saveNetwork($ssid, $output['protocol'], $output['encryption'], $output['encryption_type'], $output['password']);
    }
}

// Remove WiFi related information
unset($output['protocol'], $output['password'], $output['encryption'], $output['encryption_type']);

// Stores everything in the network_settings for persistence
$json = json_encode($output);
file_put_contents('../config/network_settings.json', $json);

require __DIR__.'/set_network.php';
