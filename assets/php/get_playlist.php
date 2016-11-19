<?php

header('Content-Type: application/json');

require 'dbconnect.php';
$albumID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$results = $mysqli->query("SELECT tracks FROM $albums WHERE id = $albumID LIMIT 1");

// TODO the hits shouldn't be increased if the
$mysqli->query("UPDATE $albums SET hits = hits + 1 WHERE id = $albumID");

try {
    // if you are a code nazi, please look away. There are some jews over there!
    $last_used_id_file = __DIR__ . '/../config/lastid';
    $last_used_id = 0;

    if (file_exists($last_used_id_file)) {
        $last_used_id = intval(file_get_contents($last_used_id_file));
        $last_used_id++;
    }

    file_put_contents($last_used_id_file, $last_used_id);

    $mysqli->query("UPDATE $albums SET last_played = $last_used_id WHERE id = $albumID");
    // Okay, you can look again!
    // No, no I HAD GOOD REASONS FOR DOING THAT, keep that gun away, sir, please, I have a family!
} catch (Exception $e) {
    error_log($e->getTraceAsString());
    exit;
} finally {
    if ($results === false) {
        echo '{"error": "true"}';
        exit;
    }

    $tracks = $results->fetch_object();

    $tracks = json_decode($tracks->tracks);

    foreach ($tracks as $key => $track) {
        $track->album = $albumID;
        $track->no = $key;
    }

    echo json_encode($tracks);
}