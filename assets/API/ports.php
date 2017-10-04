<?php

use Lib\Config;

header('Content-Type: application/json');

require_once '../../vendor/autoload.php';

$config = new Config();

$payload = file_get_contents('php://input');

// GET ALL
if (strlen($payload) == 0 && count($_GET) === 0) {
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

// BULK SET VIA JSON REQUEST
$data = json_decode($payload, true, JSON_NUMERIC_CHECK);
if ($data) {
    // TODO Add real method
    error_log(json_encode($data));

    echo json_encode($config->get('ports'));

    exit;
}

// INVALID REQUEST
echo json_encode(['invalid request']);
