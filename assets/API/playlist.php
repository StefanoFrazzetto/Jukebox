<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 10/01/2017
 * Time: 12:35.
 */
require_once '../../vendor/autoload.php';

use Lib\MusicClasses\Album;

header('Content-Type: application/json');

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$play = filter_input(INPUT_GET, 'play', FILTER_VALIDATE_BOOLEAN);

$album = Album::getAlbum($id);

if ($album != null) {
    if ($play) $album->play();

    echo json_encode($album->getSongs());
} else {
    echo '[]';
}
