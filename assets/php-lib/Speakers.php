<?php

namespace Lib;

use Exception;

/**
 * Class Speakers provides access to the device speakers.
 *
 * @author Stefano Frazzetto
 *
 * @version 1.0.0
 */
class Speakers
{
    /**
     * Return true if the speakers are on, false otherwise.
     *
     * @throws Exception if it is not possible to get the speakers
     *                   status.
     *
     * @return bool true if the speakers are on, false otherwise
     */
    public static function getStatus()
    {
        $status = OS::execute('gpio read 0');
        $status = intval($status);

        if ($status != 0 && $status != 1) {
            throw new Exception('Cannot get speakers status.');
        }

        return boolval($status);
    }

    /**
     * Turn on the speakers.
     */
    public static function turnOn()
    {
        OS::execute('gpio mode 0 out');
        OS::execute('gpio write 0 1');
    }

    /**
     * Turn off the speakers.
     */
    public static function turnOff()
    {
        OS::execute('gpio mode 0 out');
        OS::execute('gpio write 0 0');
    }
}
