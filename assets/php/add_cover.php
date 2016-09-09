<?php

ini_set("log_errors", 1);
ini_set("error_log", "php-error.log");

require '../php-lib/file-functions.php';
require '../php-lib/jpgconverter.php';

$input = INPUT_POST;
$mode = filter_input(INPUT_POST, 'coverFrom', FILTER_SANITIZE_NUMBER_INT);

switch ($mode) {
    case 0:
        $picked_cover = filter_input($input, 'uploadedCover', FILTER_SANITIZE_NUMBER_INT);

        session_start();

        $_SESSION['cover'] = $_SESSION['covers'][$picked_cover];

        convertImage($tmp_folder . $_SESSION['cover'], $tmp_folder . 'cover.jpg', 75, 300, 300); //main image
	    convertImage($tmp_folder . $_SESSION['cover'], $tmp_folder . 'thumb.jpg', 75, 150, 150); //thumnail image
        echo 0;
        break;
    case 1:
        $url = filter_input($input, 'coverURL', FILTER_VALIDATE_URL);
        $tmp_folder_ripper = $_POST['savePath'];

        if(isset($tmp_folder_ripper) && $tmp_folder_ripper !== '') {
            $tmp_folder = $tmp_folder_ripper;
        }
        
        if (!$url) {
            echo 'Invalid URL';
            exit;
        }

        $file = file_get_contents($url);

        file_put_contents($tmp_folder . 'cover.jpg', $file);

        //echo 'sono il tuo dio di canies';

        try{
        convertImage($tmp_folder . 'cover.jpg', $tmp_folder . 'cover.jpg', 75, 300, 300); //main image
        convertImage($tmp_folder . 'cover.jpg', $tmp_folder . 'thumb.jpg', 75, 150, 150); //thumnail image
        } catch(Exception $e) {
            echo 'Vi e stato un terribile errore!';
            print_r (e);
        }
        echo 0;
        break;
    default:
        echo 'Bad Request';
}

