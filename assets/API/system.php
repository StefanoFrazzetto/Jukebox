<?php

require_once '../../vendor/autoload.php';

use Lib\Calibrator;
use Lib\Speakers;
use Lib\System;

if (isset($_GET['action'])) {
    switch ($_GET['action']) {

        case 'speakers_on':
            Speakers::turnOn();
            break;

        case 'speakers_off':
            Speakers::turnOff();
            break;

        case 'shutdown':
            System::shutdown();
            break;

        case 'reboot':
            System::reboot();
            break;

        case 'getSocTemp':
            return System::getSoctemp();
            break;

        case 'runUpdate':
            return System::update() && System::upgrade();
            break;

        case 'eject':
            System::eject();
            break;

        case 'calibrate':
            Calibrator::run();
            break;

        default:
    }
}
