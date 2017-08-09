<?php

require_once '../../../../vendor/autoload.php';

use Lib\Radio;

header('Content-Type: application/json');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$name = filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING);

$url = filter_input(INPUT_GET, 'url', FILTER_VALIDATE_URL);

$cover = filter_input(INPUT_GET, 'cover', FILTER_SANITIZE_STRING);

function error($message)
{
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

/**
 * @param Radio $radio
 */
function success($radio)
{
    echo json_encode(['status' => 'success', 'cover' => $radio->getCover()]);
    exit;
}

if ($id == null) {
    error('Invalid ID provided');
}

$radio = Radio::loadRadio($id);

if ($radio == null) {
    error('Radio not found');
}

if ($name != null) {
    $radio->setName($name);
}

if ($url != null) {
    $radio->setUrl($url);
}

if ($cover != null) {
    try {
        $radio->addCover($cover);
    } catch (Exception $e) {
        error($e->getMessage());
    }
}

if ($radio->save()) {
    success($radio);
} else {
    error('Database error');
}
