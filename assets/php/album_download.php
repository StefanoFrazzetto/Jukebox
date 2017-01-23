<?php

require_once '../php-lib/Zipper.php';
require_once '../php-lib/MusicClasses/Album.php';

$outputDIR = '/var/www/html/downloads/';
$zipCheck = $outputDIR . 'zipCheck';


$albumID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if ((time() - file_get_contents($zipCheck)) > 300 && file_exists($zipCheck)) {
    unlink($zipCheck);
}

if (!file_exists($zipCheck)) {

    $album = Album::getAlbum($albumID);

    $title = $album->getTitle();
    $artists = $album->getArtistsName();

    $tracks = $album->getTracks();

    $outputFileName = preg_replace('/[^A-Za-z0-9\-]/', ' ', implode($artists, '-')) . ' - ' . preg_replace('/[^A-Za-z0-9\-]/', ' ', $title);
    $outputFile = $outputDIR . $outputFileName . '.zip';

    file_put_contents($zipCheck, time());

    if (end($tracks)->getCd() != $tracks[0]->getCd()) {
        $differentCDs = true;
        $CD = -1; //Del
    } else {
        $differentCDs = false;
    }

    foreach ($tracks as $key => $track) {

        if ($differentCDs) {
            if ($track->getCd() != $CD) {
                $CD = $track->getCd();
                //echo $CD;
            }
            $albumMap[$CD][] = $track->getUrl();
        } else {
            $CD = $track->getCd();
            $albumMap[$CD][] = $track->getUrl();
        }
    }

    if (!file_exists($outputFile) && isset($albumMap)) {
        Zipper::zip($albumMap, $outputFileName, $albumID);
    }

    while (!file_exists($outputFile)) {
        sleep(1);
    }

    $outputFile = 'http://' . $_SERVER['HTTP_HOST'] . '/downloads/' . $outputFileName . '.zip';

    $output = "<a href='" . $outputFile . "'>Click here to download the album.</a>";

    unlink($zipCheck);
} else {
    $output = 'You\'re already downloading an album. Try again later.';
}

echo $output;
