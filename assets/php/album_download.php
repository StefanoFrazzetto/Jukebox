<?php

require_once '../../vendor/autoload.php';

use Lib\Zipper;

$albumID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$output = '';

try {
    $zipper = new Zipper($albumID);
    $zipper->createZip();
    $output = "<br/><a href='".$zipper->getDownloadURL()."'><button>Download Album</button></a>";
} catch (Exception $e) {
    $output = $e->getMessage();
}

echo $output;
