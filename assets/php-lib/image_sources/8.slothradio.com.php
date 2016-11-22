<?php

function slothradio($query, $artist, $album) {
	$no = '4';
	$artist = str_replace(" ", "+", $artist);
	$album = str_replace(" ", "+", $album);
	$html = file_get_contents("http://covers.slothradio.com/?adv=&artist=$artist&album=$album");

	$dom = new DOMDocument();
	@$dom->loadHTML($html);



	$xpath = new DOMXPath($dom);
	$imgs = $xpath->evaluate("/html/body//img");

	$imageurls = [];

	for ($i = 0; $i < $no; $i++) {
		if (!$imgs->item($i) == null){
			$img = $imgs->item($i);
			$url = $img->getAttribute('src');
			$w =   $img->getAttribute('width');		
			$imageurls[$i] = "$url";
		}
	}

	array_splice($imageurls, 0, 3);

	if(!empty($imageurls)){
		return $imageurls;
	} else {
		return NULL;
	}
}