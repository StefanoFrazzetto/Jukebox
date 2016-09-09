<?php

header('Content-Type: application/json');

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

$begin_time = time();

$timeout = 4;

set_time_limit ( $timeout + 1 );

ini_set('memory_limit', '16M');

$full_address = $_GET['url'];//"http://streaming.shoutcast.com/CINEMIX-1";

$paresed_address = parse_url($full_address);

$host = $paresed_address['host'];
$request = $paresed_address['path'] . @$paresed_address['query'];
if($paresed_address['port'])
    $port = $paresed_address['port'];
else
    $port = 80;

$meta['cycles'] = 0;

start:
$meta['cycles'] ++;


$fp = fsockopen($host, $port, $errno, $errstr);

if (!$fp) {
    echo "ERROR: $errno - $errstr<br />\n";
} else {
    
    @$data.="--\r\n\r\n"; 

    $msg = 
    "GET $request HTTP/1.0
    Icy-MetaData: 1
    \r\n\r\n"; 
    
    
    fputs($fp,$msg.$data); 
    
    $header_chunk = '';

    // get the response 
    while(true){
        $header_chunk = fread($fp,1);
        @$header .= $header_chunk;
        
        if(strpos($header,"\r\n\r\n") !== false){
            break;
        }
    }
    
    preg_match_all('/\n([^:]*):\n?(.*)$/m', $header, $matches);
    
    foreach($matches[1] as $key => $name ) $meta[trim($name, " \t\n\r\0\x0B")] = trim($matches[2][$key], " \t\n\r\0\x0B");
    
    $meta_int = @$meta['icy-metaint'];
    
    if($meta_int){
        $mp3_chunk = fread($fp, $meta_int);

        if(strlen($mp3_chunk) != $meta_int && ((time() - $begin_time) < $timeout)){
            fclose($fp);
            goto start; // God of programming, please forgive me for using a goto.
        } else if((time() - $begin_time) >= $timeout){
            $meta['timeout'] = true;
            goto end;
        }
        
        $magic_byte = ord(fread($fp, 1));
        
        $meta_data = fread($fp, $magic_byte*16);   
    }
    else {
         $meta['no_meta_int'] = true;
    }
    end: 
    fclose($fp);

    //$meta['mp3_chunk'] = $mp3_chunk;

    $meta['magic_byte'] = $magic_byte;

    $meta['data_data'] = $meta_data;

    $meta['mp3_lenght'] = strlen ($mp3_chunk);
    
    echo json_encode($meta);
    /*
    echo $header;
    echo "\n = MAGIC BYTE = \n";
    echo $magic_byte;
    echo "\n = META= \n";
    echo $meta_data;
    echo "\n = MP3 CHUNK LENGHT = \n";
    
    echo strlen ($mp3_chunk);
    
    echo "\n == \n";
    echo $mp3_chunk;
    echo "\n == \n";  
   */
}
?>
