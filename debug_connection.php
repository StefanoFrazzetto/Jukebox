<pre><?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 11-Oct-16
 * Time: 18:49
 */

require "assets/php-lib/Network.php";
require "assets/php-lib/Wifi.php";

$network = new Network();

$wifi = new Wifi();

$network->load_network();

echo "<hr/>";

echo "interface: ", Wifi::getInterface(), PHP_EOL;

echo "<hr/>";
print_r($network);

echo "<hr/>Command: ";
$network->debug_connect();

echo "<hr/>Network scan ";

ob_flush();

print_r($wifi->wifiScan());