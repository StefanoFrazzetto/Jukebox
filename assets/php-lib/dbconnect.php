<?php

$dbhost = "localhost";
$dbuser = "root";
$dbpassword = "password1000";
$mydb = "zwytytws_albums";
$albums = 'albums';//This is the table

$mysqli = new mysqli($dbhost, $dbuser, $dbpassword, $mydb);

if (!$mysqli) {
    echo 'Could not connect to mysql';
    exit;
}
