<?php

// Handles the updates

use Lib\UpdaterManager;

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
$return = ['success' => true, 'message' => ''];

// Create the return array
function createReturnStatus($success, $message)
{
    return ['success' => $success, 'message' => $message];
}

switch ($action) {
    case 'update': // run the updates
        $updater = new UpdaterManager();
        if ($updater->hasErrors()) {
            $return = createReturnStatus(false, 'Cannot run the updater');
        }

        try {
            $updater->run();
        } catch (Exception $e) {
            $return = createReturnStatus(false, $e->getMessage());
        }
        break;

    default:
        $return = ['success' => false, 'message' => 'No action specified'];
}

echo json_encode($return);