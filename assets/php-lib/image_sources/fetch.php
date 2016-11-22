<?php

if(!isset($_GET['debug']) || $_POST['artist'] == 'debug') {
	$artist = urldecode($_POST['artist']);
	$album = urldecode($_POST['album']);
} else {
	$artist = urldecode($_GET['artist']);
	$album = urldecode($_GET['album']);
}

$query = "$artist $album";
$query = preg_replace('/\s+/', '+', $query);

foreach (new DirectoryIterator(__DIR__) as $file) {
	if ($file->isFile() && $file->getFilename() != basename($_SERVER['PHP_SELF'])) {
		$provider = explode(".", $file->getFilename());
		$providers[] = $provider[1];
		include_once $file->getFilename();
	}
}

// Check that the result is not null or base64
function addNonNull($provider){
	global $query, $album, $artist;
	$temp_urls = $provider($query, $artist, $album);
	if(!empty($temp_urls)) {
		foreach ($temp_urls as $key => $temp_url) {
			if(strpos($temp_url, "data:image") !== FALSE){
				unset($temp_urls[$key]);
			}
		}
		return $temp_urls;
	}
}

$urls = [];
foreach($providers as $provider) {
	$urls = array_values(array_unique(array_merge($urls, (array)addNonNull($provider))));
}

echo json_encode($urls);