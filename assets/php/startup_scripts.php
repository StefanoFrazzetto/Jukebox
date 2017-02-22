<?php

// Put all the stuff you want to execute at startup time here.
// Example: create config folders, files, etc.

use Lib\Network;
use Lib\Speakers;

if (!isset($_SESSION['started']) || !$_SESSION['started']) {
    $_SESSION['started'] = true;

    $network = new Network();

    if (!$network->isConnected()) {
        try {
            $network->load_network();
            $network->connect();
        } catch (Exception $e) {
            error_log('Failed to connect at start up because of this: '.$e->getMessage());
            error_log("Don't blame if it doesn't work, I am a good program, and I catch my exceptions.");
        }
    }

    // Turn on the speakers
    Speakers::turnOn();
}
