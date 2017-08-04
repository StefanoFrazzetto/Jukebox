<?php

require_once '../../vendor/autoload.php';

use Lib\Zipper;

$action = $_GET['action'];
$albumID = $_GET['albumID'];
$output = 'An error occurred. Please try again later.';

if (isset($action) && isset($albumID)) {
    switch ($_GET['action']) {

        case 'download':
            try {
                $zipper = new Zipper($albumID);
                $zipper->createZip();
                $output = "<br/><a href='" . $zipper->getDownloadURL() . "'><button>Download Album</button></a>";
            } catch (Exception $e) {
                $output = $e->getMessage();
            }
            break;

        case 'status':
            $zipper = new Zipper($albumID);
            $output = $zipper->getProgressPercentage();
            break;

        default:
    }
}

echo $output;