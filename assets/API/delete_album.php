<?php
header('Content-Type: application/json');

require_once '../../vendor/autoload.php';

use Lib\MusicClasses\Album;

$albumID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$album = Album::getAlbum($albumID);

if ($album == null) {
    die(json_encode(['status' => 'error', 'message' => 'album not found']));
}

$album->delete();

echo json_encode(json_encode(['status' => 'error']));