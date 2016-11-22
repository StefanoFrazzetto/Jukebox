<?php

// Put all the stuff you want to execute at startup time here.
// Example: create config folders, files, etc.

include __DIR__ . "/../php-lib/Network.php";

$network = new Network();

$network->load_network();

try {
    $network->connect();
} catch (Exception $e) {
    error_log("Failed to connect at start up because of this: " . $e->getMessage());
    error_log("Don't blame if it doesn't work, I am a good program, and I catch my exceptions.");
}