<?php

namespace Lib;

use InvalidArgumentException;

/**
 * Class OS is used to access the OS commands without invoking shell_exec
 * directly. The class provides methods to access the system's processes,
 * or to execute commands with or without output.
 */
abstract class OS
{
    /**
     * Start a service.
     *
     * @param string $name the service name
     *
     * @return bool true if the service was started, false otherwise.
     */
    public static function startService($name)
    {
        return self::executeWithResult("sudo service $name start");
    }

    /**
     * Stop a service.
     *
     * @param string $name the service name
     *
     * @return bool true if the service was stopped, false otherwise.
     */
    public static function stopService($name)
    {
        return self::execute("sudo service $name stop");
    }

    /**
     * Restart a service.
     *
     * @param string $name the service name
     *
     * @return bool true if the service was restarted, false otherwise.
     */
    public static function restartService($name)
    {
        return self::execute("sudo service $name restart");
    }

    /**
     * Return true the service is running, otherwise false.
     *
     * @param string $service_name the service name
     *
     * @return bool
     */
    public static function isServiceRunning($service_name)
    {
        $status = self::execute("sudo service $service_name status");

        return StringUtils::contains($status, 'active (running)');
    }

    /**
     * Checks if a process is running by checking if its process id is present.
     *
     * @param string $process_name The name of the process
     *
     * @return bool true if the process is currently running, false otherwise.
     */
    public static function isProcessRunning($process_name)
    {
        return self::execute("pidof -x $process_name") != '';
    }

    /**
     * Execute a command and returns its output.
     * The argument(s) can be passed as string or array.
     *
     * @param string       $command   The command to execute
     * @param string|array $arguments The argument(s) to pass
     *
     * @return string A string containing the output of the command.
     */
    public static function execute($command, $arguments = '')
    {
        if (!empty($arguments)) {
            $arguments = is_array($arguments) ? implode(' ', $arguments) : $arguments;
            $arguments = escapeshellarg($arguments);
        }

        return trim(shell_exec("$command $arguments"));
    }

    /**
     * Execute a command and return if it was successful or not.
     *
     * @param string       $command   The command or script to execute.
     * @param string|array $arguments The argument(s) to pass along with the command.
     *
     * @return bool true if the command/script has been executed successfully,
     *              false otherwise.
     */
    public static function executeWithResult($command, $arguments = '')
    {
        if (!empty($arguments)) {
            $arguments = is_array($arguments) ? implode(' ', $arguments) : $arguments;
            $arguments = escapeshellarg($arguments);
        }

        exec("$command $arguments", $out, $res);

        return $res == 0;
    }

    /**
     * Get the devices of the same type.
     *
     * @param string $type The type of device to look for.
     *
     * @return string A string containing all the devices of the searched type.
     */
    public static function getDevicesByType($type)
    {
        if (empty($type)) {
            throw new InvalidArgumentException('You need to provide the device type');
        }

        return self::execute("lsblk | grep $type | cut -d' ' -f1");
    }

    /**
     * @return string The scripts directory path.
     */
    public static function getScriptsPath()
    {
        $config = new Config();

        return $config->get('paths')['scripts'];
    }

    /**
     * Executes a command using the env passed as an array.
     * The arguments must be passed as an associative array.
     *
     * @param string $command    The command or script to execute
     * @param string $arguments  The arguments to be set in the environment
     * @param bool   $background The command/script is executed in background if this
     *                           flag is set to true. The default is false.
     *
     * @return int The process id of the command/script.
     */
    public static function executeWithEnv($command, $arguments = '', $background = false)
    {
        if (empty($arguments) || !is_array($arguments)) {
            throw new InvalidArgumentException('The second argument must be an associative array');
        }

        file_put_contents('/tmp/bestemmie-debug.log', '');
        foreach ($arguments as $key => $argument) {
            $setting = "$key=$argument";
            file_put_contents('/tmp/bestemmie-debug.log', $setting, FILE_APPEND);
            putenv($setting);
        }

        if ($background) {
            return self::executeBackgroundCommand($command);
        }

        return self::execute($command);
    }

    /**
     * Executes a command without returning its output.
     * It's possible to set whether the command should run in background or not.
     *
     * @param string $command    The command to execute
     * @param string $arguments  The arguments to pass along with the command
     * @param bool   $background Flag to set whether the task should run in
     *                           background or not.
     */
    public static function executeWithoutOutput($command, $arguments = '', $background = false)
    {
        if (!empty($arguments)) {
            $arguments = is_array($arguments) ? implode(' ', $arguments) : $arguments;
            $arguments = escapeshellarg($arguments);
        }

        if ($background) {
            self::executeBackgroundCommand('command');
        } else {
            exec("bash $command $arguments");
        }
    }

    /**
     * Executes a command in background.
     * The output will be sent to /dev/null by default, but this
     * behaviour can be overridden passing the directory where stdio
     * and/or stderr should be stored.
     *
     * @param string $command The command to be executed
     * @param string $output  The full path to the location file where
     *                        the output should be redirected.
     *
     * @return int The process id of the command/script.
     */
    public static function executeBackgroundCommand($command, $output = '/dev/null')
    {
        $process = new Process($command, $output);

        return $process->getPid();
    }
}
