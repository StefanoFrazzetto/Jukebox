<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 05/01/2017
 * Time: 10:02
 */

require_once '../php-lib/MusicClasses/Album.php';
require_once '../php-lib/MusicClasses/Artist.php';
require_once '../php-lib/MusicClasses/Song.php';
require_once '../php-lib/Radio.php';

$genres = file_get_contents('../php-lib/MusicClasses/genres.json');

header('Content-Type: application/json');

$storages = [
    "artists" => Artist::getAllArtists(),
    "albums" => Album::getAllAlbums(),
    "radios" => Radio::getAllRadios(),
    "genres" => json_decode($genres),
    "placeholder" => "/assets/img/album-placeholder.png"
];

echo json_encode($storages);