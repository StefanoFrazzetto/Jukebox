<?php

require_once '../../../../vendor/autoload.php';

use Lib\Radio;

header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$url = $_GET['url'];
$name = $_GET['name'];
$cover = $_GET['cover'];

$radio = new Radio($name, $url);

$result = $radio->save();

if ($cover != '/assets/img/album-placeholder.png') {
    $radio->addCover('/var/www/html'.$cover);
}

if (!$result) {
    echo '{"status": "error"}';
} else {
    echo '{"status": "success"}';
}
