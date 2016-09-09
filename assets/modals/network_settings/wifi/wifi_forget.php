<?php

require '../../../php-lib/wifi_utils.php';

$essid = filter_input(INPUT_GET, 'essid');

forgetNetwork($essid);