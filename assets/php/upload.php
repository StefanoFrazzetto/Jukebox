<?php

ini_set("log_errors", 1);
ini_set("error_log", "uploader-errors.log");

mb_internal_encoding("UTF-8");

require '../php-lib/file-functions.php';

// A list of permitted file extensions
$allowed_music = array('mp3');
$allowed_cover = array('jpg', 'png', 'jpeg');

if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {

    $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

    if (!in_array(strtolower($extension), $allowed_music) AND ! in_array(strtolower($extension), $allowed_cover)) {
        echo '{"status":"error"}';
        exit;
    }

    $name = $_FILES['file']['name'];
    $tmp_name = $_FILES['file']['tmp_name'];
    $sanitizedName = sanitize($_FILES['file']['name']);
    $clearName = removeExtension(utf8_decode($name));

    prevent_overwrite($tmp_folder, $sanitizedName);

    $temp_url = $tmp_folder . $sanitizedName;

    if (move_uploaded_file($tmp_name, $temp_url)) {
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        session_start();
        if (in_array(strtolower($extension), $allowed_music)) {
            require '../php-lib/phpID3.php';
        }
        if (in_array(strtolower($extension), $allowed_cover)) {
            $_SESSION['covers'][] = $sanitizedName;
        }
        echo '{"status":"success"}';
        session_write_close();
        exit;
        // Code ran successfully
    }
    echo '{"status":"error","temp_name":"', $tmp_name, '","clearname":"', $temp_url, '"}';
    exit;
}

echo '{"status":"error", "error":' . json_encode($_FILES) . '}';
