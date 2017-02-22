<?php

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

//$device = shell_exec("lsblk | grep rom | cut -d' ' -f1");
$device = 'sr0';
$discid = trim(shell_exec("discid /dev/$device"));

if ($discid != '') {
    $url = "http://musicbrainz.org/ws/2/discid/$discid?fmt=json";
    $json = @file_get_contents($url);
}

if ($json !== false) {
    $array = json_decode($json);
    $release_id = $array->releases[0]->id;
} else {
    $release_id = null;
    echo 'Not found or no connection!';
}

if ($release_id != null) {
    $json_image = file_get_contents("http://coverartarchive.org/release/$release_id");
    $json = json_decode($json_image);
    $images_array = $json->images;
    foreach ($images_array as $image) {
        $images['covers'][] = $image->image;
        $images['thumbnails']['small'][] = $image->thumbnails->small;
        $images['thumbnails']['large'][] = $image->thumbnails->large;
    }
    print_r($images);
}
