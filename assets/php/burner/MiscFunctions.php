<?php

class MiscFunctions {

	public static function calculateIntPercentage($partial, $total) {

		if($partial == 0 || $total == 0) {
			$percentage = 0 ;
		} else {
			$percentage = intval(floor(($partial/$total)*100));
		}

		return $percentage;
	}

	public static function outputJson($output) {
		echo json_encode($output);
		flush();
	}
}