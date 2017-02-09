<?php
header('Content-Type: application/json');

session_start();

require_once "../php-lib/Uploader.php";

$uploader_id = filter_input(INPUT_GET, 'uploader_id');

if ($uploader_id == null) {
    $uploader_id = session_id();
}

try {
    if (Uploader::upload($uploader_id)) {
        echo Uploader::createStatus('success');
    } else {
        echo Uploader::createStatus('error', 'it was not possible to upload the file');
    }
} catch (Exception $e) {
    echo Uploader::createStatus('error', $e->getMessage());
}