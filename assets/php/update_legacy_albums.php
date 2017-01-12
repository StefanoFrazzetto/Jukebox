<?php
/**
 * Used to update the legacy database to the new one
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 12/01/2017
 * Time: 11:30
 */

include_once '../php-lib/MusicClasses/Album.php';

foreach (Album::getAllAlbums() as $album)
    foreach ($album->getLegacySongs() as $song) {
        echo json_encode($song);
        $song->save();
        $song->addArtist($album->getLegacyArtist()->getId());
    }