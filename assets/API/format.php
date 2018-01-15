<?php

require_once '../../vendor/autoload.php';

use Lib\System;

$operation = $_GET['operation'];

if (isset($operation)) {
    $fail = 'Error! It was not possible to perform the requested action.';

    switch ($operation) {
        case 'format_hdd_database':
            $device = new System();
            $success_soft = 'The albums and the radio stations have been successfully removed.';

            echo $device->softReset() ? $success_soft : $fail;
            break;

        case 'factory_reset':
            $device = new System();
            $success_hard = 'The Jukebox has been reset to the factory settings.';

            echo $device->hardReset() ? $success_hard : $fail;
            break;

        case 'reset_factory_settings':
            $device = new System();
            $success = "All settings have been reset to factory values.\nPlease reboot the system.";

            echo $device->resetSettingsToFactoryValues() ? $success : $fail;
            break;

        default:
            error_log("Unrecognised operation $operation in file format.php.");
            echo 'An error occurred. Please try again.';
    }
}
