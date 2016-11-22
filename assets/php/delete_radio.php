<?php

require '../php-lib/Radio.php';


$radioID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// if (file_exists('../../jukebox/'.$radioID)) {
//     deleteDir('../../jukebox/'.$radioID);
// }

# delete radio files here

if ($radioID == false) {
    echo "Error parsing the given id";
    exit;
}


if (!Radio::deleteRadio($radioID)) {
    echo "Failed to delete the radio";
    exit;
}

echo "success";
