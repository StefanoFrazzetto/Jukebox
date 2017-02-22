<?php

require_once '../../../../vendor/autoload.php';

use Lib\Wifi;

$essid = filter_input(INPUT_GET, 'essid');

(new Wifi())->forgetNetwork($essid);
