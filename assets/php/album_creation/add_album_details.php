<?php

$albumTitle = utf8_decode(filter_input(INPUT_POST, 'albumTitle', FILTER_SANITIZE_STRING));
$albumArtist = utf8_decode(filter_input(INPUT_POST, 'albumArtist', FILTER_SANITIZE_STRING));

if ($albumTitle && $albumArtist) {
    session_start();

    $_SESSION['albumTitle'] = $albumTitle;
    $_SESSION['albumArtist'] = $albumArtist;
    echo 0;
    exit;
}

echo 1;
exit;
