<?php

require '../../../php-lib/Wifi.php';

$essid = filter_input(INPUT_GET, 'essid');

(new Wifi())->forgetNetwork($essid);