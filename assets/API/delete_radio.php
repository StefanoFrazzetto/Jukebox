<?php

require '../php-lib/Radio.php';

$radioID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$status = ['status' => 'error'];

if ($radioID == false || $radioID == null) {
    $status['message'] = "Error parsing the given id";
    die(json_encode($status));
}

if (!Radio::deleteRadio($radioID)) {
    $status['message'] = "Failed to delete the radio";
    die(json_encode($status));
}

$status['status'] = 'success';

echo json_encode($status);
