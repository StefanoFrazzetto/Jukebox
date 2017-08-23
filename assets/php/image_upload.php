<?php

require_once '../../vendor/autoload.php';

use Lib\Config;
use Lib\FileUtils;

$target_dir = Config::getPath('tmp_uploads').'images/';
$file = $_FILES['file'];

if (!(file_exists($target_dir))) {
    mkdir($target_dir);
}

// Delete files older than 2 hours.
foreach (glob($target_dir.'*') as $file_directory) {
    // Delete the file if older than 2 hours.
    if (filemtime($file_directory) < time() - 7200) {
        unlink($file_directory);
    }
}

$target_file = $target_dir.sha1(microtime()).basename($file['name']);
$uploadOk = 1;
$imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
// Check if image file is a actual image or fake image
if (isset($_POST['submit'])) {
    $check = getimagesize($file['tmp_name']);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        $message = 'File is not an image.';
        $uploadOk = 0;
    }
}

// Check if file already exists
if (file_exists($target_file)) {
    $message = 'Sorry, image already exists.';
    $uploadOk = 0;
}
// Check file size
if ($file['size'] > 1000000) {
    $message = 'Sorry, your image is too large.';
    $uploadOk = 0;
}
// Allow certain file formats
if ($imageFileType != 'jpg' && $imageFileType != 'png' && $imageFileType != 'jpeg' && $imageFileType != 'gif') {
    $message = 'Sorry, only JPG, JPEG, PNG & GIF images are allowed.';
    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo json_encode(['status' => 'error', 'message' => $message]);
    // if everything is ok, try to upload file
} else {
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        echo json_encode(['status' => 'success', 'cover_path' => $target_file, 'cover_url' => FileUtils::pathToHostUrl($target_file)]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Unknown error. Your file was not uploaded.']);
    }
}
