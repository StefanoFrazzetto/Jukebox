<?php

require_once "assets/php-lib/Git.php";

$git = new Git();

print_r($git::getChanges());