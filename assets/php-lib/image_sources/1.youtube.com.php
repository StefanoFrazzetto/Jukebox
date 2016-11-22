<?php

function get_string_between($string, $start, $end)
{
	$string = " " . $string;
	$ini    = strpos($string, $start);

	if ($ini == 0)
		return "";
	$ini += strlen($start);
	$len = strpos($string, $end, $ini) - $ini;
	return substr($string, $ini, $len);
}


function youtube($query){
	$html = file_get_contents("https://www.youtube.com/results?search_query=$query");

	$imgurl = get_string_between($html, "//i.ytimg.com/p/", "sddefault.jpg");
	if (!$imgurl == ""){
		$imageurls = "//i.ytimg.com/p/" . $imgurl . "sddefault.jpg";
	} else {
		$imageurls = null;
	}

	if (!$imageurls == null){
		return $imageurls;
	}
}
