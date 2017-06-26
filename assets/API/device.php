<?php

require_once '../../vendor/autoload.php';

use Lib\Speakers;

if (isset($_GET['action'])) {
    switch ($_GET['action']) {

        case 'speakers_on':
            Speakers::turnOn();
            break;

        case 'speakers_off':
            Speakers::turnOff();
            break;

        default:
    }
}
