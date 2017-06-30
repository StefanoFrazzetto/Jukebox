<?php

require_once '../../vendor/autoload.php';

use Lib\Device;
use Lib\Speakers;

if (isset($_GET['action'])) {
    switch ($_GET['action']) {

        case 'speakers_on':
            Speakers::turnOn();
            break;

        case 'speakers_off':
            Speakers::turnOff();
            break;

        case 'shutdown':
            Device::shutdown();
            break;

        case 'reboot':
            Device::reboot();
            break;

        default:
    }
}
