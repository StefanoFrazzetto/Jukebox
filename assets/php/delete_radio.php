<?php

require '../php-lib/dbconnect.php';
require '../php-lib/file-functions.php';

$radioID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// if (file_exists('../../jukebox/'.$radioID)) {
//     deleteDir('../../jukebox/'.$radioID);
// }

# delete radio files here

$result = $mysqli->query("DELETE FROM radio_stations WHERE id = $radioID LIMIT 1");

var_dump($result);