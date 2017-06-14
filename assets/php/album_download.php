<?php

require_once '../../vendor/autoload.php';
require_once '../php-lib/Zipper.php';

use Lib\MusicClasses\Album;
use Lib\MusicClasses\Song;

$outputDIR = '/var/www/html/downloads/';
$zipCheck = $outputDIR.'zipCheck';

$albumID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (file_exists($zipCheck) && (time() - file_get_contents($zipCheck)) > 300) {
    unlink($zipCheck);
}

if (!file_exists($zipCheck)) {
    $Album = Album::getAlbum($albumID);
    $Songs = Song::getSongsInAlbum($albumID);

    $title = $Album->getTitle();
    $artists = $Album->getArtistsName();

    $outputFileName = preg_replace('/[^A-Za-z0-9\-]/', ' ', implode($artists, '-')).' - '.preg_replace('/[^A-Za-z0-9\-]/', ' ', $title);
    $outputFile = $outputDIR.$outputFileName.'.zip';

    if (!file_exists($outputDIR)) {
        mkdir($outputDIR, 0755, true);
    }
    file_put_contents($zipCheck, time());

    if (end($Songs)->getCd() != $Songs[0]->getCd()) {
        $differentCDs = true;
        $CD = -1; //Del
    } else {
        $differentCDs = false;
    }

    foreach ($Songs as $key => $song) {
        if ($differentCDs) {
            if ($song->getCd() != $CD) {
                $CD = $song->getCd();
                //echo $CD;
            }
            $albumMap[$CD][] = $song->getUrl();
        } else {
            $CD = $song->getCd();
            $albumMap[$CD][] = $song->getUrl();
        }
    }

    if (!file_exists($outputFile) && isset($albumMap)) {
        Zipper::zip($albumMap, $outputFileName, $albumID);
    }

    while (!file_exists($outputFile)) {
        sleep(1);
    }

    $outputFile = 'http://'.$_SERVER['HTTP_HOST'].'/downloads/'.$outputFileName.'.zip';

    $output = "<br/><a href='".$outputFile."'><button>Download Album</button></a>";

    unlink($zipCheck);
} else {
    $output = 'You\'re already downloading an album. Try again later.';
}

echo $output;
