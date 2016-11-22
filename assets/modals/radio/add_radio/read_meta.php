<?php

header('Content-Type: application/json');

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

$begin_time = time();

$timeout = 4;

set_time_limit($timeout + 1);

ini_set('memory_limit', '16M');

$full_address = $_GET['url'];

$parsed_address = parse_url($full_address);

$host = $parsed_address['host'];
$request = $parsed_address['path'] . @$parsed_address['query'];
if ($parsed_address['port'])
    $port = $parsed_address['port'];
else
    $port = 80;

$meta['cycles'] = 0;

start:
$meta['cycles']++;

$fp = fsockopen($host, $port, $err_no, $err_str);

if (!$fp) {
    echo json_encode(["error-code" => $err_no, "message" => $err_str]);
} else {

    $data = "\r\n\r\n";

    $msg = "GET $request HTTP/1.0\r\nIcy-MetaData: 1\r\n\r\n";

    fputs($fp, $msg . $data);

    $header = '';

    // get the response 
    while (true) {
        $header_chunk = fread($fp, 1);
        $header .= $header_chunk;

        if (strpos($header, "\r\n\r\n") !== false) {
            break;
        }
    }

    preg_match_all('/\n([^:]*):\n?(.*)$/m', $header, $matches);

    foreach ($matches[1] as $key => $name) $meta[trim($name, " \t\n\r\0\x0B")] = trim($matches[2][$key], " \t\n\r\0\x0B");

    if (isset($meta['icy-metaint'])) {

        $meta_int = $meta['icy-metaint'];

        $mp3_chunk = fread($fp, $meta_int);

        if (strlen($mp3_chunk) != $meta_int && ((time() - $begin_time) < $timeout)) {
            fclose($fp);
            goto start; // God of programming, please forgive me for using a goto.
        } else if ((time() - $begin_time) >= $timeout) {
            $meta['error'] = "Request time out";
            goto end;
        }

        $magic_byte = ord(fread($fp, 1));

        // Checks whether the byte is actually an integer
        if (is_int($magic_byte)) {
            $magic_byte = intval($magic_byte);

            $meta_data = fread($fp, $magic_byte * 16);

            $meta['magic_byte'] = $magic_byte;

            //$meta['data_data'] = $meta_data;

            $meta['mp3_lenght'] = strlen($mp3_chunk);

            $re = '/([a-zA-Z\-\_0-9]*)\s*\=\s*\'([^\']*)\';/';

            preg_match_all($re, $meta_data, $sub_matches);

            foreach ($sub_matches[1] as $key => $gne) {
                $meta[$gne] = $sub_matches[2][$key];
            }
        } else {
            $meta['no_meta_int'] = true;
            $meta['error'] = "Magic byte not found";
        }
    } else {
        $meta['no_meta_int'] = true;
        $meta['error'] = "No meta int specified";
    }
    end:
    fclose($fp);

    echo json_encode($meta);
}
