<?php

require_once '../../vendor/autoload.php';

use Lib\Database;
use Lib\FileUtils;

$Database = new Database();

if (isset($_GET['operation'])) {
    $jukebox_folder = '/var/www/html/jukebox';
    $config_folder = '/var/www/html/assets/config';
    $fail = 'Error! It was not possible to perform the requested action.';

    switch ($_GET['operation']) {
        case 'format_hdd_database':
            $db_res = $Database->truncate('all');
            FileUtils::emptyDirectory($jukebox_folder);
            $success = 'The albums and the radio stations have been successfully removed.';
            echo FileUtils::isDirEmpty($jukebox_folder) && $db_res ? $success : $fail;
            break;

        case 'factory_reset':
            $Database::resetDatabase();
            FileUtils::emptyDirectory($jukebox_folder);
            // TODO Create method to reset the configuration
            $res = FileUtils::isDirEmpty($jukebox_folder);
            echo $res ? 'The Jukebox has been reset to the factory settings.' : $fail;
            break;

        default:
            echo 'An error occurred. File: format.php';
    }
}
