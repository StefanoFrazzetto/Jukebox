<?php

$mac = $_GET['mac'];

shell_exec("bash /var/www/html/assets/cmd/bluetooth/disconnect.sh");
$output = shell_exec("/var/www/html/assets/cmd/bluetooth/bluez5-connect $mac");
if (strpos($output, 'org.bluez.Error.Failed') !== false) {
    echo 'failed';
}
else{
echo "Connected";
}



?>