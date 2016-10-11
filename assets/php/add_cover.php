<?php

ini_set("log_errors", 1);


require '../php-lib/Cover.php';
require_once '../php/FileUtil.php';


$input = INPUT_POST;
$mode = filter_input(INPUT_POST, 'coverFrom', FILTER_SANITIZE_NUMBER_INT);

$tmp_folder = FileUtil::$_temp_uploads;

switch ($mode) {
    case 0:
        // From the normal upload

        $picked_cover = filter_input($input, 'uploadedCover', FILTER_SANITIZE_NUMBER_INT);

        session_start();

        $_SESSION['cover'] = $_SESSION['covers'][$picked_cover];

        $cover = new Cover($tmp_folder . $_SESSION['cover']);

        $cover->saveAlbumImagesToFolder($tmp_folder);
        echo 0;
        break;
    case 1:
        $url = filter_input($input, 'coverURL', FILTER_VALIDATE_URL);
        $tmp_folder_ripper = $_POST['savePath'];

        if (isset($tmp_folder_ripper) && $tmp_folder_ripper !== '') {
            $tmp_folder = $tmp_folder_ripper;
        }

        if (!$url) {
            echo 'Invalid URL';
            exit;
        }

        $file = file_get_contents($url);

        file_put_contents($tmp_folder . 'cover.jpg', $file);

        //echo 'sono il tuo dio di canies';

        try {
            $cover = new Cover($tmp_folder . 'cover.jpg');

            $cover->saveAlbumImagesToFolder($tmp_folder);
        } catch (Exception $e) {
            echo 'Vi e stato un terribile errore!';
            print_r(e);
        }
        echo 0;
        break;
    default:
        echo 'Bad Request';
}

