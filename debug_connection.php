<pre><?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 11-Oct-16
 * Time: 18:49.
 */
require 'vendor/autoload.php';

use Lib\Network;
use Lib\Wifi;

$network = new Network();

$start = microtime();

if (Network::isConnected()) {
    $fin = microtime();
    echo '<h1>CONNECTED. Ping '.($fin - $start).' μs</h1>';
} else {
    echo '<h1>NOT CONNECTED :(</h1>';
}

$wifi = new Wifi();

$network->load_network();

echo '<h1>WiFi interface: <i>', Wifi::getInterface(), PHP_EOL, '</i></h1>';

print_r($network);

echo '<h1>Command</h1>';
$network->debug_connect();

echo '<h1>Connected Wifi Network</h1>';
print_r($wifi->getConnectedNetwork());

ob_flush();

echo '<h1>/etc/network/interfaces</h1>';

echo file_get_contents('/etc/network/interfaces');

echo '<h1>/etc/wpa_supplicant.conf</h1>';

echo shell_exec('sudo cat /etc/wpa_supplicant.conf');

echo '<h1>WiFi Scan</h1>';

ob_flush();

print_r($wifi->wifiScan());
