<?php

session_start();

function getPostTrackByID($ID)
{
    return filter_input(INPUT_POST, 'track'.$ID, FILTER_SANITIZE_STRING);
}

$tracks = $_SESSION['tracks'];

session_write_close();

foreach ($tracks as $key => &$track) {
    $track['title'] = getPostTrackByID($key);
}

session_start();

$_SESSION['tracks'] = $tracks;

session_write_close();

echo 0;
