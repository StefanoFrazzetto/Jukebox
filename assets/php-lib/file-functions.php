<?php

#$tmp_folder = 'tmp_uploads/';
$tmp_folder = '../../jukebox/tmp_uploads/';


function stripAccents($stripAccents) {
    return strtr($stripAccents, 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ', 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
}

function sanitize($string, $force_lowercase = true, $anal = false) {
    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
        "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
        "â€”", "â€“", ",", "<", ">", "/", "?");
    $clean = trim(str_replace($strip, "", strip_tags($string)));
    $clean = preg_replace('/\s+/', "-", $clean);
    $clean = preg_replace('/-{2,}/', "-", $clean);
    $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean;
    $clean = ($force_lowercase) ?
            (function_exists('mb_strtolower')) ?
                    mb_strtolower($clean, 'UTF-8') :
                    strtolower($clean) :
            $clean;
    $clean = utf8_decode($clean);
    $clean = stripAccents($clean);
    return $clean;
}

function removeExtension($string) {
    $withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $string);
    return $withoutExt;
}

function deleteDir($dirPath) {
    if (!is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}

function trim_all($str, $what = NULL, $with = ' ') {
    if ($what === NULL) {
        //  Character      Decimal      Use
        //  "\0"            0           Null Character
        //  "\t"            9           Tab
        //  "\n"           10           New line
        //  "\x0B"         11           Vertical Tab
        //  "\r"           13           New Line in Mac
        //  " "            32           Space

        $what = "\\x00-\\x20";    //all white-spaces and control chars
    }

    return trim(preg_replace("/[" . $what . "]+/", $with, $str), $what);
}

function remove_bad_stuff(&$string) {
    /*
      $string = str_replace('ÿþ', '', $string);
      $string = str_replace("\0", '', $string);
      $string = trim_all($string);
      $string = preg_replace('/^\s+/', '', $string);
      $string = preg_replace('/\s+$/', '', $string);
     */
    return $string;
}

function add_before_extension($file, $to_be_added) {
    $extension_pos = strrpos($file, '.'); // find position of the last dot, so where the extension starts
    $file = substr($file, 0, $extension_pos) . $to_be_added . substr($file, $extension_pos);
    return $file;
}

function prevent_overwrite($path, &$filename, $iteration = 1) {
    if ($iteration != 1) {
        $filename_to_check = add_before_extension($filename, '-'.$iteration);
    } else {
        $filename_to_check = $filename;
    }

    if (file_exists($path.$filename_to_check)) {
        prevent_overwrite($path, $filename, $iteration + 1);
    } else {
        if ($iteration != 1) {
            $filename = $filename_to_check;
        }
    }
}
