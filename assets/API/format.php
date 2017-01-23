<?php

require_once "../php-lib/Database.php";
require_once "../php-lib/FileUtil.php";
require_once "../php-lib/file-functions.php";

$Database = new Database();

if (isset($_GET['operation'])) {

    $jukebox_folder = "/var/www/html/jukebox";
    $config_folder = "/var/www/html/assets/config";
    $fail = "Error! It was not possible to perform the requested action.";

    switch ($_GET['operation']) {
        case "format_hdd_database":
            $db_res = $Database->drop("all");
            FileUtil::emptyDirectory($jukebox_folder);
            $success = "The albums and the radio stations have been successfully removed.";
            echo FileUtil::isDirEmpty($jukebox_folder) && $db_res ? $success : $fail;
            break;

        case "factory_reset":
            $Database::resetDatabase();
            FileUtil::emptyDirectory($jukebox_folder);
            FileUtil::emptyDirectory($config_folder);
            $res = FileUtil::isDirEmpty($jukebox_folder) && FileUtil::isDirEmpty($config_folder);
            echo $res ? "The Jukebox has been reset to the factory settings." : $fail;
            break;

        default:
            echo "An error occurred. File: format.php";
    }

}


