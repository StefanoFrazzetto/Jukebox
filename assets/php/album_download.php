<?php

require_once '../php-lib/zipper.php';
require_once '../php-lib/dbconnect.php';

$outputDIR = '/var/www/html/downloads/';
$zipCheck = $outputDIR . 'zipCheck';


$albumID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if ((time() - file_get_contents($zipCheck)) > 300 && file_exists($zipCheck))
{
    unlink($zipCheck);
}

if (!file_exists($zipCheck)) {

    $results = $mysqli->query("SELECT * FROM $albums WHERE id = $albumID LIMIT 1");
    $result = $results->fetch_object();

    $title = $result->title;
    $artist = $result->artist;
    $tracks = json_decode($result->tracks);

    $outputFileName = preg_replace('/[^A-Za-z0-9\-]/', ' ', $artist) . ' - ' . preg_replace('/[^A-Za-z0-9\-]/', ' ', $title);
    $outputFile = $outputDIR . $outputFileName . '.zip';

    file_put_contents($zipCheck, time());

    if (end($tracks)->cd != $tracks[0]->cd) {
        $differentCDs = true;
        $CD = -1; //Del
    } else {
        $differentCDs = false;
    }

    foreach ($tracks as $key => $track) {

        if ($differentCDs) {
            if ($track->cd != $CD) {
                $CD = $track->cd;
                //echo $CD;
            }
            $albumMap[$CD][] = $track->url;
        } else {
            $CD = $track->cd;
            $albumMap[$CD][] = $track->url;
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
