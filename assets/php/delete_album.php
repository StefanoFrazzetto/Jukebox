<?php

require '../php-lib/dbconnect.php';
require '../php-lib/file-functions.php';

$albumID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (file_exists('../../jukebox/'.$albumID)) {
    deleteDir('../../jukebox/'.$albumID);
}

$result = $mysqli->query("DELETE FROM $albums WHERE id = $albumID LIMIT 1");

echo 0;