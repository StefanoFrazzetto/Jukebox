<?php

require_once 'autoload.php';

class Burner {

	// Tracks To Burn
	private $_ttb;
	private $_output_format;

	public function __construct($output_format)
	{
		$tracks = TracksHandler::getTracksJSON();
		$indexes = array_filter(array_keys($tracks), 'is_int');

		$this->_ttb = $tracks[$indexes[0]];
		$this->_output_format = $output_format;
	}

	public function burn() {
		$tracks = TracksHandler::getTracksJSON();
		$indexes = array_filter(array_keys($tracks), 'is_int');
		// Remove the tracks from the file, so it won't burn them again.
		FileUtil::removeIndexFromJson(BurnerHandler::$_burner_tracks_json, $indexes[0]);
		// Create the parent directory
		$parent = BurnerHandler::$_burner_folder;
		if(!(file_exists($parent))) {
			mkdir($parent);
		}

		// Copy the tracks
		foreach ($this->_ttb as $key => $track) {
			$track_path = FileUtil::$_albums_root . '/' .$track['album'] . '/' . $track['url'];
			$file_ext = pathinfo($track_path, PATHINFO_EXTENSION);

			$destination = $parent . '/' . $track['title'] . '.' . $file_ext;

			FileUtil::copy($track_path, $destination);
		}

		$DiscWriter = new DiscWriter();
		$DiscWriter->burnDisc(BurnerHandler::$_burner_folder, $this->_output_format);

        file_put_contents("/tmp/DEBUGGGER.TXT", "Tracks: " . implode(" - ", $this->_ttb) . "Output f: " . $this->_output_format);
	}
	
}
