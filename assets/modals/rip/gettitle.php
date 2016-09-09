<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

$device = shell_exec("lsblk | grep rom | cut -d' ' -f1");
$discid = trim(shell_exec("discid /dev/$device"));
$url = "http://musicbrainz.org/ws/2/discid/$discid?fmt=json";
$json = @file_get_contents($url);
$array = json_decode($json);

$album_title = $array->releases[0]->title;
// $tracks = $array->releases[0]->media[0]->tracks;

// echo '<pre>';
// foreach ($tracks as $temp)
// {
// 	$track[$temp->number] = array('title' => $temp->recording->title, 'number' => $temp->number, 'length' => $temp->recording->length);
// }

$temp_title = explode(':', $album_title);

// $data['artist'] = $temp_title[0];
// $data['album'] = $temp_title[1];
$_SESSION['album_title'] = $album_title;

echo $album_title;