<?php

use Lib\Config;

header('Content-Type: application/json');

require_once '../../vendor/autoload.php';

$config = new Config();

// GET ALL
if (count($_GET) === 0) {
    echo json_encode($config->get('ports'));

    exit;
}

// SET VALUE
if (isset($_GET['set'], $_GET['value'])) {
    $key = filter_input(INPUT_GET, 'set', FILTER_SANITIZE_STRING);
    $value = filter_input(INPUT_GET, 'value', FILTER_VALIDATE_INT);

    $ports = $config->get('ports');

    $ports[$key] = $value;

    $config->set('ports', $ports);

    echo json_encode($ports);

    exit;
}

echo json_encode(['invalid request']);
