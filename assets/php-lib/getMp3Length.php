<?php
function getMp3Length($file){
    $length = shell_exec('mp3info -p "%S" '.$file);  
    return $length;
}