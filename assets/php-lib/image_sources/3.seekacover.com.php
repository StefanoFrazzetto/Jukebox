<?php

function seekacover($query) {
	$no = '4';

	$html = file_get_contents("http://www.seekacover.com/cd/$query");

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
	array_splice($imageurls, 0, 2);

	if(!empty($imageurls)){
		return $imageurls;
	} else {
		return NULL;
	}
}