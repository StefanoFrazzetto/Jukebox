<?php

$interface_file = __DIR__ . '/../config/wifi_interface';
$interface = shell_exec("ls /sys/class/net | grep -v 'eth0\|lo\|tunl0'");
$wifiDB = __DIR__ . '/../config/wifiDB.json';

$wifiConfig = null;

loadFile();

if (file_exists($interface_file))
    $interface = file_get_contents($interface_file);
else {
    //Create a new database.
    $interface = newGetInterface();
    file_put_contents($interface_file, $interface);
}

if (!file_exists($wifiDB)) {
    file_put_contents($wifiDB, '{}');
}

function loadFile()
{
    global $wifiConfig;

    $data = file_get_contents(__DIR__ . '/../config/wifiDB.json');
    $wifiConfig = json_decode($data, true);
}

function newGetInterface()
{
    $interface = shell_exec("ls /sys/class/net | grep -v 'eth0\|lo\|tunl0'");
    return trim($interface, "\n");

}

function getNetwork($essid)
{
    global $wifiConfig;

    try {
        return $wifiConfig[$essid];
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Adds or update a network
 * @param $network array network associative array that should contain the password
 */
function updateNetwork($network)
{
    global $wifiConfig;

    $essid = $network['ESSID'];

    unset($network['signal'], $network['connected'], $network['saved']);

    $salt = createSalt($essid);

    $network['salt'] = $salt;

    $network['password'] = encodePassword($network['password'], $salt);

    loadFile();

    $wifiConfig[$essid] = $network;

    saveFile();
}

function forgetNetwork($essid){
    global $wifiConfig;

    loadFile();

    unset($wifiConfig[$essid]);

    saveFile();
}

function saveFile(){
    global $wifiConfig, $wifiDB;

    file_put_contents($wifiDB, json_encode($wifiConfig));
}

function getConnectedNetwork()
{
    global $interface;

    $output = shell_exec('sudo iwconfig ' . $interface);

    preg_match("/ESSID:\"(.*)\" /", $output, $matches);

    if(!isset($matches[1])){
        return null;
    }

    $essid = $matches[1];

    $matches = [];

    preg_match("/Access Point: (.*)   /", $output, $matches);

    $AP = $matches[1];

    $matches = [];

    preg_match("/\w*Security mode:([^\:]*)\n/", $output, $matches);

    $encryption = @$matches[1];

    $matches = [];

    preg_match("/Link Quality=(.*)\\//", $output, $matches);

    $quality = $matches[1];

    return ["ESSID" => $essid, "encryption" => $encryption, "signal" => $quality, "AP" => $AP, "connected" => true];
}

function wifiScan()
{

    global $interface;
    global $wifiConfig;

    shell_exec("sudo ifconfig $interface up");

    $mega_regex1 = '/\s*([^[:|=]*)[\:|=]\s*(.*)/';

    $mega_command = shell_exec("sudo iwlist $interface scanning | egrep 'ESSID|Encryption|Quality|IE'");

    $wifi_array = explode("\n", trim($mega_command));

    $wifiNetworks = array_keys($wifiConfig);

    $networks = [];

    $network_index = false;

    foreach ($wifi_array as $key => $wifi) {
        $wifi = trim($wifi);

        preg_match($mega_regex1, $wifi, $matches);

        $match_key = trim($matches[1], '""');

        $match_value = trim($matches[2], '""');


        if ($match_key == 'ESSID' && $match_value != $network_index) {
            // ESSID
            $network_index = $match_value;

            if (in_array($network_index, $wifiNetworks)) {
                $networks[$network_index]['saved'] = true;
            }

        } elseif ($match_key == 'Quality') {
            // Signal
            $match_value = str_replace("/", "", substr($match_value, 0, 3));
            $match_key = 'signal';

        } elseif ($match_key == "Encryption key") {
            // Encryption

            if ($match_value == 'off') {
                $match_value = 'open';
            }
            $match_key = 'encryption';

        } elseif ($match_key == "IE") {
            // Encryption Type

            if (has($match_value, "WPA2")) {
                $match_value = "WPA2";
            } elseif (has($match_value, 'WPA')) {
                $match_value = "WPA";
            } elseif ($networks[$network_index]['encryption'] == "open") {
                continue;
            } else {
                $match_value = "WEP";
            }
            $match_key = "encryption_type";

        }

        // Prevents values overriding
        if (isset($networks[$network_index][$match_key])) {
            continue;
        }

        // Finally adds the value to the array
        $networks[$network_index][$match_key] = $match_value;

    }

    $conn = getConnectedNetwork();

    if($conn !== null){
        $conn_essid = $conn['ESSID'];

        if (isset($networks[$conn_essid])) {
            $networks[$conn_essid]['connected'] = true;
        } else {
            $networks[$conn_essid] = $conn;
        }
    }

    return $networks;
}

function has($haystack, $needle)
{
    return strpos($haystack, $needle) !== false;
}

// ENCRYPTION

function decodePassword($password, $salt)
{
    $saltedpw = base64_decode($password);
    $password = str_replace($salt, '', $saltedpw);
    return $password;
}

function encodePassword($password, $salt)
{
    $password = base64_encode($password . $salt);
    return $password;
}

function createSalt($essid)
{
    $salt = base64_encode(sha1(microtime() . md5($essid)));
    return $salt;
}

function getNetworkPassword($essid)
{
    $network = getNetwork($essid);

    $password = decodePassword($network['password'], $network['salt']);

    return $password;
}