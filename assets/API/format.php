<?php

require_once '../../vendor/autoload.php';

use Lib\Device;

if (isset($_GET['operation'])) {
    $fail = 'Error! It was not possible to perform the requested action.';

    switch ($_GET['operation']) {
        case 'format_hdd_database':
            $device = new Device();
            $success_soft = 'The albums and the radio stations have been successfully removed.';

            echo $device->softReset() ? $success_soft : $fail;
            break;

        case 'factory_reset':
            $device = new Device();
            $success_hard = 'The Jukebox has been reset to the factory settings.';

            echo $device->hardReset() ? $success_hard : $fail;
            break;

        default:
            echo 'An error occurred. File: format.php';
    }
}
