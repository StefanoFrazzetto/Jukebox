<?php

$cmd = 'bash ./' . filter_input(INPUT_GET, 'cmd', FILTER_SANITIZE_STRING) . '.sh';

$args = filter_input(INPUT_GET, 'args', FILTER_SANITIZE_STRING);

if($args){
	$args = escapeshellarg($args);
}

$sudo = filter_input(INPUT_GET, 'sudo', FILTER_SANITIZE_STRING);

if($sudo == "1"){
	$cmd = "sudo -u bananapi ".$cmd;
}

echo `$cmd $args`;