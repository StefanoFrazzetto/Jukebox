<?php

ini_set("log_errors", 1);
ini_set("error_log", "php-error.log");

session_start();

$remove_from_cdparanoia_folder = "../modals/rip/scripts/remove_ripped.sh";
require '../php-lib/dbconnect.php';
require '../php-lib/file-functions.php';

// tmp_folder from ripper
if (isset($_SESSION['tmp_folder'])) {
    $tmp_folder = $_SESSION['tmp_folder'];
}

$album = $_SESSION['albumTitle'];
$artist = $_SESSION['albumArtist'];

$tracks = $_SESSION['tracks'];
session_write_close();

$json_tracks = str_replace('\'', '\\\'', json_encode($tracks));

$tracks_no = count($tracks);

$result = $mysqli->query("INSERT INTO albums (title,artist,tracks_no,tracks) VALUES('$album', '$artist','$tracks_no','$json_tracks')");
if (mysqli_error($mysqli)) {
    echo '-1';
    die;
} else {
    $result = $mysqli->query("SELECT LAST_INSERT_ID()");

    $id = $result->fetch_assoc();

    $id = $id['LAST_INSERT_ID()'];

    if ($id !== FALSE) {


        unset($_SESSION['covers'][$picked_cover]);

        foreach ($_SESSION['covers'] as $cover) {
            unlink($tmp_folder . $cover);
        }

        session_destroy();

        file_put_contents("gazzi.amari.txt", $tmp_folder);

        $cmd = 'mv "' . $tmp_folder . '" "../../jukebox/' . $id . '"';
        exec($cmd);

        exec($remove_from_cdparanoia_folder);

        //rename("tmp_uploads", "../../jukebox/$id");
        echo $id;
        exit;
    }

    echo '-1';
    die();
}
echo '-1';