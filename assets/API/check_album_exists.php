<?php

/**
 * Returns an array of albums that have the same title as the get parameter "title".
 */

header('Content-Type: application/json');

require_once '../../vendor/autoload.php';

use Lib\MusicClasses\Album;

$in_title = filter_input(INPUT_GET, 'title', FILTER_SANITIZE_STRING);

$albums = Album::findAlbumByTitle($in_title);

echo json_encode($albums);