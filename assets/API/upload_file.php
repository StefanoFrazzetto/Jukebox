<?php

session_start();

require_once "../php-lib/Uploader.php";

$upload_id = filter_input(INPUT_GET, 'upload_id');

if ($upload_id == null) {
    $upload_id = session_id();
}

try {
    if (Uploader::upload($upload_id)) {
        echo Uploader::createStatus('success');
    } else {
        echo Uploader::createStatus('error', 'it was not possible to upload the file');
    }
} catch (Exception $e) {
    echo Uploader::createStatus('error', $e->getMessage());
}