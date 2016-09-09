<?php

require_once "CommandExecuter.php";
require_once "Database.php";
require_once "FileUtil.php";
require_once "../php-lib/file-functions.php";

$Database = new Database();

if(isset($_GET['operation'])) {

	switch ($_GET['operation']) {
		case "format_hdd_database":
			$Database->drop("all");
			FileUtil::emptyDirectory("/var/www/html/jukebox");
			echo "The albums and the radio stations have been successfully removed.";
			break;

		case "factory_reset":
			$Database->drop("all");
			FileUtil::emptyDirectory("/var/www/html/jukebox");
			echo "The Jukebox has been reset to the factory settings.";
			break;

		default:
			echo "An error occurred. File: format.php";
			break;
	}

}


