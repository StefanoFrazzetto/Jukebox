<?php
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

session_start();

$ripping_folder = '/var/www/html/jukebox/cdparanoia';
// Si deve creare ogni volta - Fuck you, Vittorio.
$encoding_folder = '/var/www/html/jukebox/tmp_uploads';
$scripts = './scripts';
$ripping_file = "$ripping_folder/ripping.json";


// Recupera le informazioni di base dalla sessione.
if(!isset($_SESSION['ripping']['device']) || !isset($_SESSION['ripping']['discid'])){
	$device = shell_exec("$scripts/device.sh");
	$discid = trim(shell_exec("discid /dev/$device"));

	$_SESSION['ripping'] = array('device' => $device, 'discid' => $discid);
} else {
	$device = $_SESSION['ripping']['device'];
	$discid = $_SESSION['ripping']['discid'];
}

$url = "http://musicbrainz.org/ws/2/discid/$discid?inc=aliases+recordings&fmt=json";
$json = @file_get_contents($url);
$array = json_decode($json);


// Recupera da MusicBrainz (web service 2) le info sulle tracce contenute nel CD.
function getTracksInfo(){
	global $json, $array;

	if($json !== FALSE){

		$track['artalb'] = $array->releases[0]->title;
		$tracks = $array->releases[0]->media[0]->tracks;

		foreach ($tracks as $temp)
		{
			$track[$temp->number] = array('title' => $temp->recording->title, 'number' => $temp->number, 'length' => $temp->recording->length);
		}
	} else {
		$track = FALSE;
	}
	return $track;
}

// Return an array containing the links of the cover and the thumbnails.
function getAlbumCovers(){
	global $json, $array;

	if($json !== FALSE){
		$release_id = $array->releases[0]->id;
	} else {
		$release_id = null;
	}

	if($release_id != null){
		$json_image = @file_get_contents("http://coverartarchive.org/release/$release_id");
		if($json_image !== FALSE){
			$json = json_decode($json_image);
			$images_array = $json->images;
			foreach($images_array as $image){
				$images['covers'][] = $image->image;
				$images['thumbnails']['small'][] = $image->thumbnails->small;
				$images['thumbnails']['large'][] = $image->thumbnails->large;
			}
			return $images;
		} else {
			return NULL;
		}
	}
}

$_SESSION['ripping']['totalTracks'] = intval(trim(shell_exec("$scripts/totalTracks.sh")));
$totalTracks = $_SESSION['ripping']['totalTracks'];
$track_info = getTracksInfo();
$images = getAlbumCovers();

// Rip the CD to the output folder.
// -X: If the read skips due to imperfect data,	a  scratch,  whatever,
// abort  reading  this  track.  If output is to a file, delete the
// partially completed file.