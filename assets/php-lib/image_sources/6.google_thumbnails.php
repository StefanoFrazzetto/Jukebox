<?php

function google_thumbnails($query) { 
	$no = '10';
	$html = file_get_contents("https://www.google.co.uk/search?q=$query&tbm=isch&tbs=isz:l");

	$dom = new DOMDocument();
	@$dom->loadHTML($html);


	$xpath = new DOMXPath($dom);
	$imgs = $xpath->evaluate("/html/body//img");

	$imageurls = [];

	for ($i = 0; $i < $no; $i++) {
		if (!$imgs->item($i) == null){
			$img = $imgs->item($i);
			$url = $img->getAttribute('src');		
			$imageurls[$i] = $url;
		}
	}

	if(!empty($imageurls)){
		return $imageurls;
	} else {
		return NULL;
	}
}