<?php

/**
 * Returns an array of albums that have the same title as the get parameter "title".
 */

header('Content-Type: application/json');

require_once '../php-lib/MusicClasses/Album.php';

$in_title = filter_input(INPUT_GET, 'title', FILTER_SANITIZE_STRING);

$albums = Album::findAlbumByTitle($albumID);

echo json_encode($albums);