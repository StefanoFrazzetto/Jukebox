<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 05/01/2017
 * Time: 10:02
 */

$start = microtime(true);

require_once "../../vendor/autoload.php";

use Lib\MusicClasses\Album;
use Lib\MusicClasses\Artist;
use Lib\Radio;

$genres = file_get_contents('../php-lib/MusicClasses/genres.json');

header('Content-Type: application/json');

$storages = [
    "artists" => Artist::getAllArtists(),
    "albums" => Album::getAllAlbums(),
    "radios" => Radio::getAllRadios(),
    "genres" => json_decode($genres),
    "placeholder" => "/assets/img/album-placeholder.png",
];

$storages['time'] = microtime(true) - $start;

echo json_encode($storages);