<?php

header('Content-Type: application/json');

require 'dbconnect.php';
$albumID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$results = $mysqli->query("SELECT tracks FROM $albums WHERE id = $albumID LIMIT 1");

$mysqli->query("UPDATE $albums SET hits = hits + 1 WHERE id = $albumID");


try {
    // if you are a code nazi, please look away. There are some jews over there!
    $last_used_id = $mysqli->query("SELECT last_used_id FROM time_machine LIMIT 1")->fetch_object()->last_used_id;

    $last_used_id++;

    $mysqli->query("UPDATE $albums SET last_played = $last_used_id WHERE id = $albumID");

    $mysqli->query("UPDATE time_machine SET last_used_id = $last_used_id");
} finally {
    // Okay, you can look again!
    // No, no I HAD GOOD REASONS FOR DOING THAT, keep that gun away, sir, please, I have a family!
    $tracks = $results->fetch_object();

    $tracks = json_decode($tracks->tracks);

    foreach ($tracks as $key => $track) {
        $track->album = $albumID;
        $track->no = $key;
    }

    echo json_encode($tracks);
}