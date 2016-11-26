<?php

require_once 'rip_functions.php';

// Constants
$ripper['total_tracks'] = $total_tracks;
$ripper['cd_title'] = getCDTitle();

function percentage($partial, $total){
    if ($partial == 0 || $total == 0) {
        $percentage = 0 ;
    } else {
        $percentage = intval(floor(($partial/$total)*100));
    }
    return $percentage;
}

function ajaxRipping () {
    global $ripper;
    $processed = countRippedTracks();
    $ripper = array_merge($ripper, array("status" => "ripping", "processed_tracks" => $processed, "percentage" => percentage($processed, $ripper['total_tracks'])));
    echo json_encode($ripper);
    //var_dump(json_encode($ripper));
}

function ajaxEncoding () {
    global $ripper;
    $processed = countEncodedTracks();
    $ripped = countRippedTracks();
    $ripper['total_tracks'] = $ripped;
    $ripper = array_merge($ripper, array("status" => "encoding", "processed_tracks" => $processed, "percentage" => percentage($processed, $ripped)));
    echo json_encode($ripper);
}

if (!isset($_SESSION['CD'])) {
    $_SESSION['CD'] = 1;
}

$folder = "CD" . $_SESSION['CD'];

// Check if cdparanoia is running
if( isRipping() ) {

    ajaxRipping();

} else {

    // Check if lame is running or there are already some ripped tracks but no encoded tracks
    if ( isEncoding() ) {
        
        ajaxEncoding();

    } else {

        // It's not encoding, then:
        // Let's check if there's something already ripped, otherwise start the ripping process.

        // Count the encoded tracks inside the folder
        $encoded = countEncodedTracks() == 0;
        $encoded_in_folder = countEncodedTracks($folder) == 0;

        $anything_encoded = $encoded && $encoded_in_folder;

        if ( countRippedTracks() == 0 && $anything_encoded) {

            startRipping();

        } elseif ( countRippedTracks() != 0 && $anything_encoded ) {

            // There're already ripped tracks and it's not ripping, let's encode them.
            startEncoding();

        } elseif ( !$anything_encoded ) {

            // RECAP: It is not ripping, not encoding, there are both ripped and encoded tracks:
            // We can now proceed to set the track names and the album covers URLs into the SESSION.
            // setSessionCoversURLS();
            // setSessionTracks();

            $ripper['status'] = 'completing the process';
            echo json_encode($ripper);
        }
    }
}

