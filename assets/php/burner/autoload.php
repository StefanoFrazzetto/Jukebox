<?php

session_start();

require_once "../CommandExecuter.php";
require_once "../../php-lib/Database.php";
require_once "../../php-lib/FileUtil.php";
require_once "Burner.php";
require_once "DiscWriter.php";
require_once "MiscFunctions.php";
require_once "TracksHandler.php";
require_once "BurnerHandler.php";
require_once "../../php-lib/Utility.php";

$burner_folder = BurnerHandler::$_burner_folder;
$scripts = BurnerHandler::$_burner_abs_scripts;
