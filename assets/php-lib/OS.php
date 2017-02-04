<?php

require_once __DIR__ . "/Config.php";

/**
 * Class OS is used to access the OS commands without invoking shell_exec
 * directly. The class provides methods to access the system's processes,
 * or to execute commands with or without output.
 */
abstract class OS
{
    /**
     * Checks if a process is running by checking if its process id is present.
     *
     * @param string $process_name The name of the process
     * @return bool true if the process is currently running, false otherwise.
     */
    public static function isProcessRunning($process_name)
    {
        if (self::execute("pidof -x $process_name") != "") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string The scripts directory path.
     */
    public static function getScriptsPath()
    {
        $config = new Config();
        return $config->get("paths")["scripts"]["path"];
    }

    /**
     * Executes a command and returns its output.
     * The argument(s) can be passed as string or array.
     *
     * @param string $command The command to execute
     * @param string|array $arguments The argument(s) to pass
     * @return string A string containing the output of the command.
     */
    public static function execute($command, $arguments = "")
    {
        if (!empty($arguments)) {
            $arguments = is_array($arguments) ? implode(" ", $arguments) : $arguments;
            $arguments = escapeshellarg($arguments);
        }

        return trim(shell_exec("$command $arguments"));
    }

    /**
     * Executes a command without returning its output.
     * It's possible to set whether the command should run in background or not.
     *
     * @param string $command The command to execute
     * @param string $arguments The arguments to pass along with the command
     * @param bool $background Flag to set whether the task should run in
     * background or not.
     * @return void
     */
    public static function executeWithoutOutput($command, $arguments = "", $background = false)
    {
        if (!empty($arguments)) {
            $arguments = is_array($arguments) ? implode(" ", $arguments) : $arguments;
            $arguments = escapeshellarg($arguments);
        }

        if ($background) {
            self::runBackgroundCommand("command");
        } else {
            exec("$command $arguments");
        }

        return;
    }

    /**
     * Executes a command in background.
     * The output will be sent to /dev/null by default, but this
     * behaviour can be overridden passing the directory where stdio
     * and/or stderr should be stored.
     *
     * @param string $command The command to be executed
     * @param string $stdio The full path to the location where
     * <b>stdio</b> should be redirected
     * @param string $stderr The full path to the location where
     * <b>stderr</b> should be redirected
     * @return void
     */
    public static function runBackgroundCommand($command, $stdio = "/dev/null", $stderr = "/dev/null")
    {
        exec("$command > $stdio 2>$stderr &");
        return;
    }

}