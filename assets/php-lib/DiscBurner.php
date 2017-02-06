<?php

require_once "Disc.php";

class DiscBurner extends Disc
{
    /**
     * Initializes the attributes needed by the DiscWriter.
     */
    protected function __init()
    {
        // TODO: Implement init() method.
    }

    /**
     * @param $directory - the directory containing the tracks
     * @param $output_format - the tracks output format
     * @param  $output_log_dir - the directory where the log file will be stored
     */
    public function burnDisc($directory, $output_format, $output_log_dir = "/tmp")
    {
        putenv("input_directory=$directory");
        putenv("device=$this->device_path");
        putenv("output_format=$output_format");
        putenv("output_log_dir=" . $output_log_dir);

        shell_exec($this->scripts_dir . "burner-handler.sh > /tmp/burner-errors.log 2>&1 &");

    }


}