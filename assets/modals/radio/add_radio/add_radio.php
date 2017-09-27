<?php

require_once '../../../../vendor/autoload.php';

use Lib\Radio;

header('Content-Type: application/json');

$url = filter_input(INPUT_GET, 'url', FILTER_SANITIZE_STRING);
$name = filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING);
$cover = filter_input(INPUT_GET, 'cover', FILTER_SANITIZE_STRING);

$radio = new Radio($name, $url);

$result = $radio->save();

if ($cover && $cover != '/assets/img/album-placeholder.png') {
    $radio->addCover('/var/www/html' . $cover);
}

$return = [
    'status' => !$result ? 'error' : 'success',
    'radio' => $radio
];

echo json_encode($return);
