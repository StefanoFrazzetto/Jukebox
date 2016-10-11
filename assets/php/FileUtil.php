<?php

// require_once 'autoload.php';

/**
* Created by Stefano Frazzetto - 08 Jun 2016
* https://github.com/stefanofrazzetto

* Last update: 15 Jul 2016

NOTE : ABASTRACTION NEEDED FOR $SCRIPTS
*/
abstract class FileUtil
{

	public static $_albums_root = "/var/www/html/jukebox/";
	public static $_temp = "/tmp/";
    public static $_temp_uploads = "/var/www/html/jukebox/tmp_uploads/";

	public static function isDirEmpty($dir) {
		if(count(glob($dir . "/*", GLOB_NOSORT)) === 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function emptyDirectory($pathToDirectory) {
		$res1 = shell_exec("rm -rf $pathToDirectory/*");
		$res2 = shell_exec("rm -rf $pathToDirectory/.[!.]*");

		return "$res1 $res2";
	}

	public static function renameFile($path, $file_name, $new_name) {
		$cmd = "mv $path/$file_name $path/$new_name";
		return shell_exec($cmd);
	}

	public static function removeDirectory($pathToDirectory) {
		$cmd = "rm -rf $pathToDirectory";
		return shell_exec($cmd);
	}

	public static function removeFile($pathToFile) {
		$cmd = "rm -f $pathToFile";
		return shell_exec($cmd);
	}

	public static function removeByExtension($dir, $ext) {
		$cmd = "rm -rf $dir/*.$ext";
		return shell_exec($cmd);
	}

	public static function getJson($file) {
		$content = file_get_contents($file);

		if($content === FALSE) {
			return null;
		}

		return json_decode($content, true);
	}

	public static function countFiles($directory, $ext) {
		return count(glob($directory . '/*.' . $ext, GLOB_NOSORT));
	}

	public static function removeIndexFromJson($json_file, $index) {
		$file = file_get_contents($json_file);
		$json = json_decode($file, true);

		unset($json[$index]);

		$json = json_encode($json);

		return file_put_contents($json_file, $json);
	}

	public static function saveFile($file_name, $data) {
		$file_path = $file_name;
		return file_put_contents($file_path, $data);
	}

	public static function move($origin, $destination) {
		$cmd = "nice mv $origin $destination 2>&1 &";
		shell_exec($cmd);
	}

	public static function copy($origin, $destination) {
		$cmd = "nice cp $origin $destination 2>&1 &";
		shell_exec($cmd);
	}

	/**
	*	Return the directory size. Includes the subdirs sizes.
	*
	*	@param $directory = "The final directory";
	*	@param $root = "Parent path";
	*	@return $size in MB.
	*/
	public static function getDirectorySize($directory, $root = "albums") {
		if($root == "albums"){
			$directory = self::$_albums_root . $directory;
		}

		if(!is_dir($directory)) {
			return null;
		}

		$io = popen ( '/usr/bin/du -sk ' . $directory, 'r' );
		$size = fgets ( $io, 4096);
		$size = substr ( $size, 0, strpos ( $size, "\t" ) );
		pclose ( $io );

		return ceil($size / 1000);
	}

	/**
	*	Get the total size by an array of tracks.
	*
	*	@param $tracks: array of tracks.
	*	@return Total size in KB.
	*/
	public static function getTracksSize($tracks) {
		$total_size = 0;
		foreach ($tracks as $track) {
			$track_path = self::$_albums_root . $track;
			$total_size += filesize($track_path);
		}

		return ceil($total_size / 1000);
	}

	/**
	*	Get the size of a file.
	*
	*	@param $tracks: array of tracks.
	*	@return Total size in KB.
	*/
	public static function getFileSize($file_path) {

		$size = filesize($file_path);

		return ceil($size / 1000);
	}
	
	/**
	*	Return the track length in seconds.
	*/
	public static function getTrackLength($track_path) {
		return shell_exec("mp3info -p '%S' $track_path 2>&1");
	}
	
}