<?php

require_once "Config.php";

/**
 * Class DiscWriter handles the disc writer operations...
 *
 * @author Stefano Frazzetto - https://github.com/StefanoFrazzetto
 * @version 1.0.0
 */
class DiscWriter
{
    private $device_name = null;
    private $device_path = null;
    private $disc_id = null;

    private $total_tracks = null;
    private $disc_size = null;

    private $scripts_path;

    public function __construct()
    {
        $this->device_name = trim(shell_exec("lsblk | grep rom | cut -d' ' -f1"));
        $this->device_path = "/dev/$this->device_name";

        $this->disc_id = $this->getTrimmedCmd("discid $this->device_path");

        $Config = new Config();
        $this->scripts_path = $Config->get("burner_path") . "scripts/";
    }

    /**
     * Returns the trimmed output of the passed command.
     *
     * @param $cmd - the command to execute
     * @return string - the trimed output of the command
     */
    private function getTrimmedCmd($cmd)
    {
        return trim(shell_exec($cmd));
    }

    /**
     * Returns the device name.
     *
     * @return string - the device name
     */
    public function getDeviceName()
    {
        return $this->device_name;
    }

    /**
     * Returns the device path.
     *
     * @return string - the device path
     */
    public function getDevicePath()
    {
        return $this->device_path;
    }

    /**
     * TODO:
     * Returns the disc size in ?.
     *
     * @return int - the disc size
     */
    public function getDiscSize()
    {
        if ($this->disc_size === null) {
            $this->disc_size = 0;
        }

        return $this->disc_size;
    }

    /**
     * Checks if a disc is blank.
     * Returns true if the disc is blank, false otherwise.
     *
     * @return bool - true if the disc is blank, false otherwise
     */
    public function checkDiscBlank()
    {
        $command = CommandExecutor::raw(CommandExecutor::$scripts_folder . "CdCheck.sh");

        if (strpos($command, '<<No disc in drive>>') !== false) {
            return false;
        } elseif (strpos($command, 'DVD') !== false) {
            $this->checkBlank();
        } elseif (strpos($command, 'CD-ROM') !== false) {
            return false;
        } elseif (strpos($command, 'CD') !== false) {
            return true;
        }

        return false;
    }

    private function checkBlank()
    {
        $cmd = shell_exec("lsblk | grep rom | awk {'print $4'} | sed 's/[^0-9]*//g'");
        $disc_check = shell_exec("wodim -v dev=/dev/sr0 -toc 2>&1");

        if ($cmd < 10 && substr_count($disc_check, "Cannot load media") == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the number of tracks on the CD/DVD.
     *
     * @return int - the number of tracks on the CD/DVD
     */
    public function getTracksCount()
    {
        if ($this->total_tracks === null) {
            $this->total_tracks = $this->getTrimmedCmd("cdparanoia -sQ -d $this->device_path |& grep -P -c \"^\s+\d+\.\" | grep -E '[0-9]'");
        }

        return $this->total_tracks;
    }

    /**
     * @param $directory - the directory containing the tracks
     * @param $output_format - the tracks output format
     * @param  $output_log_dir - the directory where the log file will be stored
     */
    public function burnDisc($directory, $output_format, $output_log_dir = "/tmp")
    {
        putenv("input_directory=$directory");
        putenv("device=$this->device_name");
        putenv("output_format=$output_format");
        putenv("output_log_dir=" . $output_log_dir);

        shell_exec($this->scripts_path . "burner-handler.sh > /tmp/burner-errors.log 2>&1 &");

    }

}