<?php

header('Content-Type: application/json');

require_once "../php-lib/ImageFetcher.php";

$artist = urldecode($_GET['artist']);
$album = urldecode($_GET['album']);
$image_type = urldecode($_GET['image_type']);

$images = new ImageFetcher($artist, $album);

echo json_encode($images->getAll());