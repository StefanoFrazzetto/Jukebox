<pre>
<?php

error_reporting(-1);

echo file_get_contents('holonet.midichlorians.it');

exec("../bin/discid", $lol);

scan:

preg_match("/([^\s]*)$/", $lol[0], $matches);

$cdid = $matches[0];

if($cdid == ''){
    sleep(5);
    goto scan;
}

echo file_get_contents("http://musicbrainz.org/ws/2/cdstub/?query=discid:$cdid");



echo $cdid;

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, "http://musicbrainz.org/ws/2/cdstub/?query=discid:$cdid");
$result = curl_exec($ch);
curl_close($ch);

/*
$obj = json_decode($result);
echo $result->access_token;
*/

print_r ($result);
