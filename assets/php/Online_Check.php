<?php
$online = shell_exec("ping -c 1 -w 1 8.8.8.8");

if (strpos($online, '100%') !== false) {
	echo '0';
}
else {
	echo '1';
}

?>
