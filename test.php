<?php

require_once "assets/php-lib/ImageFetcher.php";

header('Content-Type: text/json');

$image = new ImageFetcher("Metallica", "Master of puppets");

print_r($image->getAll());