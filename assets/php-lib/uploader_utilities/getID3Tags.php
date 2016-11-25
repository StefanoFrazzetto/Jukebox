<?php

function readRow($table, $row)
{
    //^[[:alnum:]]+:\W(.*)$
    $pattern = '/\n' . $row . ':\W(.*)\n/';
    if (preg_match($pattern, $table, $matches) !== FALSE) {
        return $matches[1];
    } else
        return FALSE;
}

function preg_remove($pattern, &$string)
{
    $string = preg_replace($pattern, '', $string);
}

function getID3Tags($file)
{
    if (file_exists($file)) {
        $cmd = "id3v2 -R \"$file\"";
        $output = shell_exec($cmd);
        $return = new stdClass();
        $return->title = readRow($output, 'TIT2');
        $return->album = readRow($output, 'TALB');
        $return->artist = readRow($output, 'TPE1');
        $return->track = readRow($output, 'TRCK');
        preg_remove('/[^0-9]+.*$/', $return->track);
        $return->track = intval($return->track);
        $return->year = readRow($output, 'TYER');
        preg_remove('/[^0-9].*$/', $return->year);
        $return->genre = readRow($output, 'TCON');
        preg_remove('/\W*\([0-9]+\)$/', $return->genre);

        if ($CD = readRow($output, 'TPOS')) {
            $return->cd = $CD;
            preg_remove('/[^0-9]+.*$/', $return->cd);
            $return->cd = intval($return->cd);
        }

        return $return;
    } else {
        return FALSE;
    }
}
