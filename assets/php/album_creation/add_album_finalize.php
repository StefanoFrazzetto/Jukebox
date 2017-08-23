<?php

ini_set('log_errors', 1);
ini_set('error_log', '../../../logs/album_finalize.log');

session_start();

$remove_from_cdparanoia_folder = '../../modals/rip/scripts/remove_ripped.sh';

require_once '../../../vendor/autoload.php';
require_once '../../php-lib/file-functions.php';

use Lib\MusicClasses\Album;
use Lib\MusicClasses\Artist;
use Lib\MusicClasses\Song;

// tmp_folder from ripper
if (isset($_SESSION['tmp_folder'])) {
    $tmp_folder = $_SESSION['tmp_folder'];
}

$title = $_SESSION['albumTitle'];
$artist = $_SESSION['albumArtist'];

$tracks = $_SESSION['tracks'];

$json_tracks = str_replace('\'', '\\\'', json_encode($tracks));

$tracks_no = count($tracks);

//$result = $mysqli->query("INSERT INTO albums (title,artist,tracks_no,tracks) VALUES('$album','$artist','$tracks_no','$json_tracks')");

$album = new Album();

$album->setTitle($title);

$album->save();

if ($album->getId() != null) {
    $id = $album->getId();

    // Artist
    $_artist = Artist::softCreateArtist($artist);

    // tracks
    foreach ($tracks as $track) {
        $song = Song::newSongFromJson((object) $track, $id);

        //        error_log(json_encode($song));
        //        error_log(json_encode($track));
        $song->save();
        $song->addArtist($_artist->getId());
    }

    // Handle files
    //unset($_SESSION['covers'][$picked_cover]);

    if (isset($_SESSION['covers']) && is_array($_SESSION['covers'])) {
        foreach ($_SESSION['covers'] as $cover) {
            if ($cover != 'cover.jpg') {
                unlink($tmp_folder.$cover);
            }
        }
    }

    session_destroy();

    $cmd = 'mv "'.$tmp_folder.'" "../../../jukebox/'.$id.'"';
    exec($cmd);

    exec($remove_from_cdparanoia_folder);

    //rename("tmp_uploads", "../../jukebox/$id");
    echo $id;
    exit;
}

echo '-1';
die();
