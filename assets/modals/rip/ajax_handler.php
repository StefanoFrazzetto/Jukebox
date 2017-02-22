<?php

require_once 'rip_functions.php';

// Constants
$ripper['total_tracks'] = $total_tracks;
$ripper['cd_title'] = getCDTitle();

function percentage($partial, $total)
{
    // Fix division by zero error
    $partial = intval($partial);
    $total = intval($total);

    if ($partial == 0 || $total == 0) {
        $percentage = 0;
    } else {
        $percentage = intval(floor(($partial / $total) * 100));
    }

    return $percentage;
}

/**
 * Count the ripped tracks and outputs status, number of ripped tracks and the percentage.
 */
function ajaxRipping()
{
    global $ripper;
    $processed = countRippedTracks();
    $ripper = array_merge($ripper, ['status' => 'ripping', 'processed_tracks' => $processed, 'percentage' => percentage($processed, $ripper['total_tracks'])]);

    outputMessage('ripping', $processed, percentage($processed, $ripper['total_tracks']));
}

/**
 * Count the encoded track and outputs status, number of encoded tracks and the percentage.
 */
function ajaxEncoding()
{
    global $ripper;
    $ripped = countRippedTracks();
    $encoded = countEncodedTracks();
    $ripper['total_tracks'] = $ripped;

    outputMessage('encoding', $encoded, percentage($encoded, $ripped));
}

/**
 * Outputs the json encoded message.
 *
 * @param $status
 * @param int $proc_tracks
 * @param int $percentage
 */
function outputMessage($status, $proc_tracks = 0, $percentage = 0)
{
    global $ripper;
    $res = ['status' => $status, 'processed_tracks' => $proc_tracks, 'percentage' => $percentage];
    $res = array_merge($ripper, $res);

    echo json_encode($res);
}

if (!isset($_SESSION['CD'])) {
    $_SESSION['CD'] = 1;
}

$folder = 'CD'.$_SESSION['CD'];

// Check if cdparanoia is running
if (isRipping()) {
    ajaxRipping();
} else {

    // Check if lame is running or there are already some ripped tracks but no encoded tracks
    if (isEncoding()) {
        ajaxEncoding();
    } else {

        // It's not encoding, then:
        // Let's check if there's something already ripped, otherwise start the ripping process.

        // Count the encoded tracks inside the folder
        $encoded = countEncodedTracks() == 0;
        $encoded_in_folder = countEncodedTracks($folder) == 0;

        $anything_encoded = $encoded && $encoded_in_folder;

        if (countRippedTracks() == 0 && $anything_encoded) {
            startRipping();
            outputMessage('starting the process');
        } elseif (countRippedTracks() != 0 && $anything_encoded) {

            // There're already ripped tracks and it's not ripping, let's encode them.
            startEncoding();
            outputMessage('encoding');
        } elseif (!$anything_encoded) {

            // RECAP: It is not ripping, not encoding, there are both ripped and encoded tracks:
            // We can now proceed to set the track names and the album covers URLs into the SESSION.
            // setSessionCoversURLS();
            // setSessionTracks();

            $ripper['status'] = 'completing the process';
            echo json_encode($ripper);
        }
    }
}
