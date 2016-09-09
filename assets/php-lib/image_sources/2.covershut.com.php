<?php

function covershut($query) {
	$no = '1';

	$html = file_get_contents("http://www.covershut.com/cover-tags.html?covertags=$query&search=Search");

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

	array_splice($imageurls, 0, 0);

	if(!empty($imageurls)){
		return $imageurls;
	} else {
		return NULL;
	}
}