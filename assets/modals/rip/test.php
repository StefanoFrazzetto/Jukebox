<?php

session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

$scripts = '/var/www/html/assets/modals/rip/scripts';
$ripping_folder = '/var/www/html/jukebox/cdparanoia';
$encoding_folder = '/var/www/html/jukebox/ripper_encoded';

$musicbrainz_json = "/$ripping_folder/musicbrainz.json";
$devicedisc_json = "/$ripping_folder/devicedisc.json";

$tracklist_json = "/$encoding_folder/tracklist.json";

function dirToArray($dir)
{
    $result = [];

    $cdir = scandir($dir);
    foreach ($cdir as $key => $value) {
        if (!in_array($value, ['.', '..'])) {
            if (is_dir($dir.DIRECTORY_SEPARATOR.$value)) {
                $result[$value] = dirToArray($dir.DIRECTORY_SEPARATOR.$value);
            } else {
                $result[] = $value;
            }
        }
    }

    return $result;
}

$folders = dirToArray($encoding_folder);

$i = 1;
foreach ($folders as $key => $item) {
    if (strpos($key, 'CD') !== false) {
        $cd_folder = $key;
        $cd_no = str_replace('CD', '', $key);
    }

    if (is_array($item)) {
        foreach ($item as $file) {

            // Se il file é mp3
            if (strpos($file, '.mp3') !== false) {
                if ($track_info !== false) {
                    // Se c'e' connessione:
                    $length = $track_info[$i]['length'] / 60000;
                    $CD[$cd_folder][] = ['title' => $track_info[$i]['title'], 'track_no' => $track_info[$i]['number'], 'url' => "$cd_folder/$file", 'cd' => $cd_no, 'length' => $length];
                } else {
                    // Se NON c'e' connessione:
                    $CD[$cd_folder][] = ['title' => basename($item), 'track_no' => $i, 'url' => "$cd_folder/$file", 'cd' => $cd_no, 'length' => 0];
                }
                $i++;
            }
        }
    }
}

var_dump($CD);
