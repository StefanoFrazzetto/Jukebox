<?php

require_once 'rip_functions.php';

$artist = $_POST['artist'];
$album = $_POST['album'];

$images = '';

function getFromCD() {
	setSessionCoversURLS();
	$artist_album = explode(':', getCDTitle());
	$images['artist'] = $artist_album[0];
	$images['album'] = $artist_album[1];
	foreach (@$_SESSION['covers'] as $value) {
		$images['url'][] = $value; 
	}
	foreach (@$_SESSION['thumbnails']['small'] as $value) {
		$images['url'][] = $value; 
	}
	foreach (@$_SESSION['thumbnails']['large'] as $value) {
		$images['url'][] = $value; 
	}
	return json_encode($images);
}

function getFromGoogle() {
	global $artist, $album;
	$query = urlencode($artist.$album);
	$size = 'medium';

	$html = file_get_contents("https://www.google.co.uk/search?q=$query&tbm=isch&tbs=isz:l&rsz=$size");

	$dom = new DOMDocument();
	@$dom->loadHTML($html);

	$xpath = new DOMXPath($dom);
	$hrefs = $xpath->evaluate("/html/body//img");

	$imageurls = [];

	for ($i = 0; $i < $no; $i++) {
		$href = $hrefs->item($i);
		$url = $href->getAttribute('src');

		$imageurls[$i] = $url;
	}

	return json_encode($imageurls);
}

function getFromAmazon() {
	return 'Amazon';
}

$images = getFromCD();
if($images === NULL) {
	$images = getFromGoogle();
	if($images === NULL) {
		$images = getFromAmazon();
	} else {
		echo "No images.";
	}
}

echo $images;