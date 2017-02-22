<?php

require '../../php-lib/file-functions.php';

if (file_exists($tmp_folder)) {
    deleteDir($tmp_folder);
}

mkdir($tmp_folder, 0777);

session_start();
session_destroy();
