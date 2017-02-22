<?php

// Some old code recycled. YAY!

$fullstring = shell_exec("export DISPLAY=':0'; xinput_calibrator");
$parsed = str_replace(',', ' ', preg_replace('/\s+/', '', str_replace("\n", '', get_string_between($fullstring, 'Setting calibration data:', '-->'))));

if ($parsed == '' || !ctype_digit(str_replace(' ', '', $parsed))) {
    exit();
}

$content = file_get_contents('/usr/share/X11/xorg.conf.d/10-evdev.conf');
$filename = '/usr/share/X11/xorg.conf.d/10-evdev.conf';
$string1 = 'Calibration';
$string2 = '        Option "Calibration" "'.$parsed.'"';
$lines = file($filename);
$changed = false;

for ($n = 0; $n < count($lines); ++$n) {
    $check = strpos($lines[$n], $string1);

    if ($check !== false) {
        $lines[$n] = $string2."\n";
        $changed = true;
        break;
    }
}

if ($changed) {
    file_put_contents($filename, $lines);
}

function get_string_between($string, $start, $end)
{
    $string = ' '.$string;
    $ini = strpos($string, $start);

    if ($ini == 0) {
        return '';
    }
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;

    return substr($string, $ini, $len);
}
