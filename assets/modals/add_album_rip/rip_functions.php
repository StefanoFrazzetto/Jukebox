<?php

session_start();

/**
 * Created by Stefano 25/01/2015
 * Last update: 19/04/2016 22:28
 */

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

$scripts = '/var/www/html/assets/modals/rip/scripts';
$ripping_folder = '/var/www/html/jukebox/cdparanoia';
$encoding_folder = '/var/www/html/jukebox/ripper_encoded';

$musicbrainz_json = "/var/www/html/jukebox/cdparanoia/musicbrainz.json";
$devicedisc_json = "/var/www/html/jukebox/cdparanoia/devicedisc.json";
$tracklist_json = "/var/www/html/jukebox/ripper_encoded/tracklist.json";

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

function clearSession() {
    unset($_SESSION['tracks']);
    unset($_SESSION['album_title']);
    unset($_SESSION['cd_title']);
    unset($_SESSION['albumArtist']);
    unset($_SESSION['albumTitle']);
    unset($_SESSION['covers']);
}


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
    clearSession();
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

// SESSION CD

if (!isset($_SESSION['CD'])) {
    $_SESSION['CD'] = 1;
}

$cdnumber = (int)$_SESSION['CD'];

/**
 * Get the CD Title from MusicBrainz_array
 *
 */
function getCDTitle()
{
    global $musicbrainz_array;

    $album_title = $musicbrainz_array->releases[0]->title;
    return utf8_encode((string)$album_title);
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
function countEncodedTracks($folder = "")
{
    global $scripts, $cdnumber;

    $encoded_tracks = intval(trim(shell_exec("$scripts/encoded_tracks.sh $folder")));
    if ($encoded_tracks == "") {
        $encoded_tracks = 0;
    }

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
    delRipped();
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
    $_SESSION['cd_title'] = $track_info['artalb'];
    $temp_title = explode(':', $track_info['artalb']);
    $_SESSION['albumArtist'] = is_null($temp_title[0]) ? ' ' : $temp_title[0];
    $_SESSION['albumTitle'] = is_null($temp_title[1]) ? ' ' : $temp_title[1];
}


function getTrackList() {
    global $encoding_folder;

    $tracklist_json = "$encoding_folder/tracklist.json";

    if(file_exists($tracklist_json)) {
        $tracklist_json = file_get_contents($tracklist_json);
        $tracklist = json_decode($tracklist_json, true);
    } else {
        $tracklist = FALSE;
    }
    
    // Array
    return $tracklist;
}

function setSessionTrackList() {
    $tracklist = getTrackList();

    foreach ($tracklist as $value) {
        $_SESSION['tracks'][] = $value;
    }
}

function moveTracksCovers($origin, $destination) {
    @mkdir($destination);
    exec("mv $origin/*.mp3 $destination");
}

// Create or edit the json file.
function putTrackList($tracks){
    global $cdnumber, $encoding_folder, $tracklist_json, $scripts;

    $tracklist_file = getTrackList();

    $cd = "CD$cdnumber";
    if($tracklist_file === FALSE || $tracklist_file == "null") {
        $tracklist_file = $tracks;
    } else {
        $tracklist_keys = array_keys($tracks);
        $tracklist_file_keys = array_keys($tracklist_file);
        $missing_keys = array_diff($tracklist_keys, $tracklist_file_keys);
        
        if(!empty($missing_keys)) {
        	foreach($missing_keys as $key) {
               $tracklist_file[$key] = $tracks[$key];
           }
       }
   }

   file_put_contents("$encoding_folder/tracklist.json", json_encode($tracklist_file));
}
// **************** //


function dirToArray($dir) { 

    $result = array(); 

    $cdir = scandir($dir, SCANDIR_SORT_DESCENDING); 
    foreach ($cdir as $key => $value) 
    { 
        if (!in_array($value,array(".",".."))) 
        { 
            if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) 
            { 
                $result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value); 
            } 
            else 
            { 
                $result[] = $value; 
            } 
        } 
    } 

    return $result; 
}

function handleTracksAndInfo() {
    global $musicbrainz_array, $encoding_folder, $tracklist_json, $cdnumber;

    $track_info = getTrackInfo();
    setSessionAlbum($track_info);

    // Upload directory or
    $_SESSION['tmp_folder'] = $encoding_folder . "/";
    $destination_folder = "$encoding_folder/CD$cdnumber/";

    if(!file_exists($destination_folder)) {
        // Crea la cartella di destinazione e ci inserisce le tracce
        moveTracksCovers($encoding_folder, $destination_folder);
    }

    $folders = dirToArray($encoding_folder);
    $i = 1;
    foreach ($folders as $key => $item) {

        if(strpos($key, 'CD') !== FALSE) {
            $cd_folder = $key;
            $cd_no = str_replace('CD', '', $key);
        }

        if(is_array($item)) {
        	// Array reverse otherwhise the tracks will be renamed in the opposite order.
            foreach(array_reverse($item) as $file) {

            // Se il file é mp3
                if (strpos($file, '.mp3') !== FALSE) {

                    if ($track_info !== FALSE && $track_info[$i]['title'] != null) {
                // Se c'e' connessione:
                        $length = round($track_info[$i]['length'] / 1000);
                        $track = array('title' => $track_info[$i]['title'], 'track_no' => $track_info[$i]['number'], 'url' => "$cd_folder/$file", 'cd' => $cd_no, 'length' => $length);
                        $tracks[$cd_folder][] = $track;
                    } else {
                // Se NON c'e' connessione:
                        $track = array('title' => $file, 'track_no' => $i, 'url' => "$cd_folder/$file", 'cd' => $cd_no, 'length' => 0);
                        $tracks[$cd_folder][] = $track;
                    }
                    $i++;
                }
            }
        }
    }
  
    putTrackList($tracks);
}

/**
 * Sets the tracks' info into the $_SESSION
 */
function setSessionTracks()
{
    global $encoding_folder;
    handleTracksAndInfo();

    $tracklist = getTrackList();

    if (is_array($tracklist) || is_object($tracklist)) {
        foreach ($tracklist as $CD) {
            foreach($CD as $track) {
                $_SESSION['tracks'][] = array('title' => $track['title'], 'track_no' => $track['track_no'], 'url' => $track['url'], 'cd' => $track['cd'], 'length' => $track['length']);
            }
        }
    }

    //var_dump($_SESSION['tracks']);

}