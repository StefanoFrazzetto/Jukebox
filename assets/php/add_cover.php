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

        if (isset($_SESSION['covers'][$picked_cover]) && is_string($_SESSION['covers'][$picked_cover])) {
            $_SESSION['cover'] = $_SESSION['covers'][$picked_cover];

            $cover = new Cover($tmp_folder . $_SESSION['cover']);

            $cover->saveAlbumImagesToFolder($tmp_folder);
        }

        echo 0;
        break;
    case 1:
        $url = filter_input($input, 'coverURL', FILTER_VALIDATE_URL);


        if (isset($_POST['savePath']) && $_POST['savePath'] !== '') {
            $tmp_folder = $_POST['savePath'];;
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
            echo 'A fatal error has happened.', PHP_EOL;
            print_r($e);
        }
        echo 0;
        break;
    default:
        echo 'Bad Request';
}

