<?php
/**
 * Created by PhpStorm.
 * User: vittorio
 * Date: 01/10/17
 * Time: 15:38.
 */

namespace Lib;

use Exception;

/**
 * They asked what the Calibrators do,
 * I'll be honest here, I've got no clue.
 *
 * But one thing I can most surely tell,
 * Touch and screen do seem to ring a bell.
 *
 * If you now fail to click something down,
 * Don't look for me, I'm so outta town.
 *
 * You can try running the script again,
 * Cause recoding it would be a pain!
 *
 *
 * @author Vittorio, coder and poet.
 */
class Calibrator
{
    private static $innerPattern = "/[0-9\s]*/";
    private static $pattern = "/(?<=Option\s\"Calibration\"\s\")([0-9\s]*)(?=\")/m";
    private static $config = '/usr/share/X11/xorg.conf.d/10-evdev.conf';
    private static $default = '1984 111 135 1928';

    /**
     * Sets the default values to the configuration file.
     */
    public static function reset()
    {
        self::setConfiguration(static::$default);
    }

    /**
     * Sets the calibration to the persistent file.
     *
     * @param $values string representing calibration values
     *
     * @throws Exception if the argument is not valid.
     */
    public static function setConfiguration($values)
    {
        if (preg_match(static::$innerPattern, $values) !== 1) {
            throw new Exception("Invalid argument exception in setConfiguration with value `$values`.");
        }

        $contents = file_get_contents(static::$config);

        $contents = preg_replace(static::$pattern, $values, $contents);

        OS::execute('sudo chmod 777 '.static::$config);

        file_put_contents(static::$config, $contents);
    }

    /**
     * Runs the calibration wizard and saves the settings in the persistence file.
     */
    public static function run()
    {
        // RUN THE KOMAND!
        // PARSE THE KOMAND!
        // STICK IT IN YOUR HEAR!

        $output = static::runScreenCalibrationCommand();
        error_log($output);
        $value = static::parseTheOutput($output);
        error_log($value);
        static::setConfiguration($value);
    }

    /**
     * Starts the calibration wizard on screen.
     *
     * @return string command output
     */
    private static function runScreenCalibrationCommand()
    {
        return shell_exec("export DISPLAY=':0'; xinput_calibrator");
    }

    /**
     * Extracts the calibration output from the wizard.
     *
     * @param $output string output.
     *
     * @throws Exception
     *
     * @return string the calibration value
     */
    private static function parseTheOutput($output)
    {
        preg_match(static::$pattern, $output, $matches);

        if (!isset($matches[1])) {
            throw new Exception('Unable to parse the output!');
        }

        return $matches[1];
    }
}
