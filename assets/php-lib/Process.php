<?php

namespace Lib;


/**
 * An easy way to keep in track of external processes.
 *
 * Linux only.
 *
 * @author Peec
 * @author Stefano Frazzetto
 */
class Process
{
    /** @var  int The process id */
    private $pid;

    /** @var string The command or path to script to execute */
    private $command;

    /** @var string The path to the file where the output of the process will be saved */
    private $output_file;

    /**
     * Create a new instance of a process.
     *
     * If the first parameters is not empty, the process will be spawned.
     * If the second parameter is not empty, it will be used as output file.
     *
     * @param string $cl The command or path to script to execute.
     * @param string $output_file The path to the file where the output of the
     * process will be saved.
     */
    public function __construct($cl = "", $output_file = "/dev/null")
    {
        if (!empty($cl)) {
            $this->command = $cl;
            $this->output_file = $output_file;
            $this->runCom();
        }
    }

    /**
     * Run the command or script and set the process id of the new process.
     */
    private function runCom()
    {
        $command = 'nohup bash ' . $this->command . " > $this->output_file 2>&1 & echo \$!";
        exec("$command", $op);
        $this->pid = (int)$op[0];
        return true;
    }

    /**
     * Return the process id of the process.
     *
     * @return int The process id of the process.
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Set the process id.
     *
     * @param int $pid The process id.
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
    }

    /**
     * Start the process.
     *
     * @return bool true if the process is started successfully,
     * false otherwise.
     */
    public function start()
    {
        if ($this->command != '') {
            return $this->runCom();
        } else {
            return false;
        }
    }

    /**
     * Stop the process.
     *
     * @return bool true if the process is stopped, false otherwise.
     */
    public function stop()
    {
        $command = 'kill ' . $this->pid;
        exec($command);
        if ($this->status() == false) return true;
        else return false;
    }

    /**
     * Return the status of the process.
     *
     * @return bool true if the process is running, false otherwise.
     */
    public function status()
    {
        $command = 'ps -p ' . $this->pid;
        exec($command, $op);
        if (!isset($op[1])) return false;
        else return true;
    }
}