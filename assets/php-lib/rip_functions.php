<?php

session_start();

/**
 * Created by Stefano 25/01/2015
 */

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);


unset($_SESSION);

$scripts = '/var/www/assets/modals/rip/scripts';
$ripping_folder = '/var/www/jukebox/cdparanoia';
$encoding_folder = '/var/www/jukebox/tmp_uploads';
$musicbrainz_json = "/$ripping_folder/musicbrainz.json";
$devicedisc_json = "/$ripping_folder/devicedisc.json";


function storeDeviceDiscInfo ($data) {
    global $devicedisc_json;
    file_put_contents($devicedisc_json, $data);
}

function readDeviceDiscInfo () {
    global $devicedisc_json, $scripts;
    $devicedisc_info = @file_get_contents($devicedisc_json);
    $devicedisc_info_decoded = json_decode($devicedisc_info, true);

    if ($devicedisc_info === FALSE || $devicedisc_info_decoded['device'] == null || $devicedisc_info_decoded['total_tracks'] == 0) {
        $data['device'] = trim(shell_exec("$scripts/device.sh"));
        $device = $data['device'];
        $data['discid'] = trim(shell_exec("discid /dev/$device"));
        $data['total_tracks'] = intval(trim(shell_exec("$scripts/totalTracks.sh")));
        storeDeviceDiscInfo(json_encode($data));
    }
    return $devicedisc_info_decoded;
}

$devicedisc_info = readDeviceDiscInfo();

$device = (string)$devicedisc_info['device'];
$discid = (string)$devicedisc_info['discid'];
$total_tracks = (int)$devicedisc_info['total_tracks'];


function storeMusicBrainzInfo ($data) {
    global $musicbrainz_json;
    file_put_contents($musicbrainz_json, $data);
}


function readMusicBrainzInfo () {
    global $musicbrainz_json;
    $musicbrainz_info = @file_get_contents($musicbrainz_json);
    if ($musicbrainz_info !== FALSE && $musicbrainz_info != '') {
        return json_decode($musicbrainz_info);
    } else {
        return FALSE;
    }
}

/**
 * Get Info from MusicBrainz
 * @return array(key => value)
 *
 */
function getMusicBrainzInfo()
{
    $musicbrainz_array = readMusicBrainzInfo();

    if($musicbrainz_array === FALSE) {
        global $discid;
        $query = "http://musicbrainz.org/ws/2/discid/$discid?inc=aliases+recordings&fmt=json";
        $json = @file_get_contents($query);
        storeMusicBrainzInfo($json);
        $musicbrainz_array = json_decode($json);
    }

    return $musicbrainz_array;
}

$musicbrainz_array = getMusicBrainzInfo();


/**
 * Get the CD Title from MusicBrainz_array
 *
 */
function getCDTitle()
{
    global $musicbrainz_array;

    $album_title = $musicbrainz_array->releases[0]->title;
    return (string)$album_title;
}

/**
 * Count the tracks in $ripping_folder;
 * @return int
 */
function countRippedTracks()
{
    global $scripts;
    $ripped_tracks = intval(trim(shell_exec("$scripts/ripped_tracks.sh")));
    return $ripped_tracks;
}

/**
 * Count the tracks in $encoding_folder
 * @return int
 */
function countEncodedTracks()
{
    global $scripts;
    $encoded_tracks = intval(trim(shell_exec("$scripts/encoded_tracks.sh")));
    return $encoded_tracks;
}

/**
 * Checks if cdparanoia is running
 * @return bool
 */
function isRipping()
{
    if (shell_exec("pidof -x cdparanoia") != "") {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if lame is running
 * @return bool
 */
function isEncoding()
{
    if (shell_exec("pidof -x lame") != "") {
        return true;
    } else {
        return false;
    }
}

/**
 * Removes the ripped tracks from $ripping_folder
 */
function delRipped()
{
    global $scripts;
    exec("$scripts/remove_ripped.sh 2>&1 &");
}

/**
 * Removes the encoded tracks from $encoding_folder
 */
function delEncoded()
{
    global $scripts;
    exec("$scripts/remove_encoded.sh 2>&1 &");
}

/**
 * Starts the ripping process
 */
function startRipping()
{
    global $scripts;
    $_SESSION['ripping']['status'] = 'ripping';
    exec("$scripts/rip.sh | at now >> /tmp/cdparanoia_log.log 2>&1 &");
}

/**
 * Starts the encoding process
 */
function startEncoding()
{
    global $scripts;
    $_SESSION['ripping']['status'] = 'encoding';
    exec("$scripts/encode.sh | at now >> /tmp/lame_log.log 2>&1 &");
}

/**
 * Parses the album covers and assign their URL to $images
 */
function setSessionCoversURLS()
{
    global $musicbrainz_array;

    if ($musicbrainz_array !== FALSE) {
        $release_id = $musicbrainz_array->releases[0]->id;
    } else {
        $release_id = null;
    }

    if ($release_id != null) {
        $json_image = @file_get_contents("http://coverartarchive.org/release/$release_id");
        if ($json_image !== FALSE) {
            $json = json_decode($json_image);
            $images_array = $json->images;
            foreach ($images_array as $image) {
                $_SESSION['covers'][] = $image->image;
                $_SESSION['thumbnails']['small'][] = $image->thumbnails->small;
                $_SESSION['thumbnails']['large'][] = $image->thumbnails->large;
            }
        }
    }
}

function getTrackInfo()
{
    global $musicbrainz_array;

    $track['artalb'] = $musicbrainz_array->releases[0]->title;
    $tracks = $musicbrainz_array->releases[0]->media[0]->tracks;
    if(is_array($tracks)){
        foreach (@$tracks as $temp)
        {
            $track[$temp->number] = array('title' => $temp->recording->title, 'number' => $temp->number, 'length' => $temp->recording->length);
        }
    }
    return $track;
}


/**
 * Sets the album info in $_SESSION
 *
 */
function setSessionAlbum($track_info)
{
    $temp_title = explode(':', $track_info['artalb']);
    $_SESSION['possible_artist'] = is_null($temp_title[0]) ? '' : $temp_title[0];
    $_SESSION['possible_album'] = is_null($temp_title[1]) ? '' : $temp_title[1];
}


/**
 * Sets the tracks' info into the $_SESSION
 */
function setSessionTracks()
{
    global $musicbrainz_array, $encoding_folder;

    $content = new FilesystemIterator($encoding_folder, FilesystemIterator::SKIP_DOTS);
    $i = 1;
    $track_info = getTrackInfo();
    setSessionAlbum($track_info);
    foreach ($content as $track) {
        if (pathinfo($track, PATHINFO_EXTENSION) == 'mp3') {
            // Se c'e' connessione:
            if ($track_info !== false) {
                $_SESSION['tracks'][] = array('title' => $track_info[$i]['title'], 'track_no' => $track_info[$i]['number'], 'url' => basename($track), 'cd' => 1, 'length' => $track_info[$i]['length']);
                // Se NON c'e' connessione:
            } else {
                $_SESSION['tracks'][] = array('title' => basename($track), 'track_no' => $i, 'url' => basename($track), 'cd' => 1, 'length' => 0);
            }
            $i++;
        }
    }
}