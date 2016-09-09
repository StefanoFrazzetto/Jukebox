<?php

function setID3Tag($file, $tag, $value) {
    $cmd = "id3v2 --$tag \"$value\" \"$file\"";

    $output = shell_exec($cmd);
}
