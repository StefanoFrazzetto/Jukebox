<?php

/**
*	Created by Stefano Frazzetto - https://github.com/StefanoFrazzetto
*	
*	Last update: 15 Jul 2016
*/

require_once 'autoload.php';

class TracksHandler {

	private $_media_size;
	private $_database;
	private $_output_format;
	private $_cds;

	/**
	*	Create the "Burner" using albums: initialise $DiscWriter and $_albums[ALBUM_ID]['size'];
	*	
	*	@param $type: Specifies if $values will contain an array of albums or a json of tracks.
	*/
	public function __construct($type = "", $values, $output_format)
	{
		// If the type of operation is not specified, exit with CODE 1.
		if($type == "") {
			exit(1);
		}

		$this->_disc_writer = new DiscWriter();
		$this->_database = new Database();

		switch ($type) {
			case "playlist":
			$this->playlist($values);
			break;

			case "albums":
			$this->manyAlbums($values, $output_format);
			break;
			
			default:
			exit(1);
		}

		// Save the created array to a tmp file.
		$cds_json = json_encode($this->_cds);
		FileUtil::saveFile(BurnerHandler::$_burner_tracks_json, $cds_json);
	}

	public static function getTracksJSON() {
		return FileUtil::getJson(BurnerHandler::$_burner_tracks_json);
	}

	public static function removeBurnedTracksJSON($index) {
		$cds_json = json_decode(BurnerHandler::$_burner_tracks_json, true);
		unset($cds_json[$index]);
		FileUtil::saveFile(BurnerHandler::$_burner_tracks_json, $cds_json);
	}

	/**
	*	Burn a playlist.
	*	
	*	@param $playlist = json encoded playlist;
	*/
	private function playlist($playlist) {
		$this->_cds = self::groupTracks($playlist, "playlist");
	}

	/**
	*	Burn many albums at once.
	*	
	*	@param $albums = array('album1_id', 'album2_id');
	*/
	private function manyAlbums($albums, $output_format) {

		$tracks = array();

		foreach($albums as $album_id) {

			$size = FileUtil::getDirectorySize($album_id);
			
			$info = array('title', 'artist', 'tracks');
			$album_info = $this->_database->select($info, "albums", "WHERE `id` = $album_id");

			if($size == null || $album_info == null) {
				continue;
			}

			$tmp_array = json_decode($album_info[0]->{'tracks'}, true);
			
			// MAY BE REMOVED BEFORE THE DEPLOYMENT.
			foreach ($tmp_array as $key => $value) {
				$tmp_array[$key]['album'] = $album_id;
			}

			$tracks = array_merge($tracks, $tmp_array);

		}

		$this->_cds = self::groupTracks($tracks, "albums", $output_format);

	}

	public function getRequiredCDS() {
		return count($this->_cds);
	}

	public function getCompilationSize() {
		$indexes = array_filter(array_keys($this->_cds), 'is_int');
		return $this->_cds[$indexes[0]]['size'] / 1000;
	}

	/**
	 *	Group the tracks together calculating the size of each group of tracks.
     * @param $tracks
     * @param string $type
     * @param string $output_format
     * @return array
     */
	private function groupTracks($tracks, $type = "", $output_format = "") {
		$audio_cd = false;
		$cd_no = 1;
		$cds = array();
		$playlist_index = 01;

		// Check if the user wants to burn an audio CD.
		if($type == "albums" && $output_format == "wav") {
			$audio_cd = true;
		}

		$DiscWriter = new DiscWriter();
		if($audio_cd) {
			// 80 minutes for audio CD
			$media_size = 80 * 60;
		} else {
			// Whole disc size for MP3
			$media_size = $DiscWriter->getDiscSize();
		}

		foreach ($tracks as $key => $track) {

			// Skip the track if either album or url is not set.
			if(!isset($track['album']) || !isset($track['url'])) {
				continue;
				// $output['status'] = "Error";
				// $output['message'] = "Track album or url not defined. Source: burner/TracksHandler.php";
				// $string = json_encode($output, true);
				// exit($string);
			}

			
			$track['title'] = sprintf("%02d", $playlist_index) . "-" . str_replace([' ', '-'], "_", $track['title']);
			
			
			$playlist_index++;

			$cd_size = & $cds[$cd_no]['size'];

			// If: the track has a CD number different than the $cd_no,
			// it will be put in another "group".
			// Else: if the size of the current group is larger than the media size,
			// the next tracks will be put in $cd_no + 1; 

			// Updated on 15/07/2016:
			// If the user is burning an audio CD, the total length of the tracks
			// must be less than 80 minutes.

			$cd_capacity = $media_size - ($media_size * 2) / 100;

			if($cd_size >= $cd_capacity) {
				$cd_no++;
			} elseif ($audio_cd) {
				$cd_no = $track['cd'];
			}

			$cds[$cd_no][] = $track;

			$track_path = FileUtil::$_albums_root . '/' . $track['album'] . '/' . $track['url'];

			if(!isset($cd_size))
				$cd_size = 0;

			if($audio_cd) { 
				$cd_size += FileUtil::getTrackLength($track_path);
			} else {
				$cd_size += FileUtil::getFileSize($track_path);
			}

		}

		// $cd_size = 0;
		// foreach ($cds as $cd) {
		// 	if($cd['size'] < $media_size)
		// }

		return $cds;
	}

}