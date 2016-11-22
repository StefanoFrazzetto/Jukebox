<?php

function get_cover($id,$scale = false) {
	$name = ($scale) ? "thumb.jpg" : "cover.jpg";
    $pictureURL = 'jukebox/' . $id . '/' . $name;
    if (!file_exists('../../' . $pictureURL)) {
        return 'assets/img/album-placeholder.png';
    }
    return $pictureURL;
}
