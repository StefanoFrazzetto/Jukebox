<?php

require __DIR__.'/getID3Tags.php';
require __DIR__.'/getMp3Length.php';

$tags = getID3Tags($tmp_folder.$sanitizedName);

$this_track = [];

$this_album = remove_bad_stuff($tags->album);

if ($this_album) {
    if (isset($_SESSION['possible_albums'])) {
        if (!in_array($this_album, $_SESSION['possible_albums'])) {
            $_SESSION['possible_albums'][] = $this_album;
        }
    }
}

$this_artist = remove_bad_stuff($tags->artist);

if ($this_artist) {
    if (isset($_SESSION['possible_artist'])) {
        if (!in_array($this_artist, $_SESSION['possible_artist'])) {
            $_SESSION['possible_artist'][] = $this_artist;
        }
    }
}

$title = remove_bad_stuff($tags->title);
$track_no = $tags->track;

if ($title) {
    $this_track['title'] = $title;
} else {
    $this_track['title'] = $clearName;
}

if ($track_no) {
    $this_track['track_no'] = $track_no;
}

if (isset($tags->cd)) {
    $this_track['cd'] = $tags->cd;
} else {
    if (isset($_SESSION['CD'])) {
        $this_track['cd'] = $_SESSION['CD'];
    } else {
        $this_track['cd'] = 1;
    }
    require __DIR__.'/setID3Tag.php';
    setID3Tag($tmp_folder.$sanitizedName, 'TPOS', $this_track['cd']);
}

$this_track['url'] = $sanitizedName;

$this_track['length'] = getMp3Length($tmp_folder.$sanitizedName);

$_SESSION['tracks'][] = $this_track;
