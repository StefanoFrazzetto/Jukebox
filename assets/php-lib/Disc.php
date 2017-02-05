<?php

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/DiscStatus.php';
require_once __DIR__ . '/OS.php';

/**
 * Class DiscWriter handles the disc writer operations...
 *
 * @author Stefano Frazzetto - https://github.com/StefanoFrazzetto
 * @version 1.0.0
 */
abstract class Disc extends DiscStatus
{
    /** @var string The rom device path, i.e.: /dev/sr0 */
    protected $device_path;

    /** @var  string The disc_id for this cd/dvd */
    protected $disc_id;

    /** @var  int The total number of tracks on the this cd/dvd */
    protected $total_tracks;

    /** @var  float This disc size */
    protected $disc_size;

    /** @var  string The path to the directory containing the scripts */
    protected $scripts_path;

    /**
     * @var  string The path to the directory that stores the log files for
     * every operation related to the disc device
     */
    protected $logs_path;

    /**
     * @var string The path to the directory where the output files for the
     * specific operation will be stored
     */
    protected $output_path;

    public function __construct()
    {
        parent::__construct();

        $this->scripts_path = $this->config['scripts'];

        $device_name = OS::execute("lsblk | grep rom | cut -d' ' -f1");
        $this->device_path = "/dev/$device_name";

        self::__init();
    }

    protected abstract function __init();


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
        if (empty($this->disc_size)) {
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
    public function isDiscBlank()
    {
        $command = OS::execute("wodim -atip -v dev=$this->device_path | grep 'Current:'| awk {'print $3'}");

        if (strpos($command, "CD") !== false) {
            return true;
        }

        return false;
    }

    protected function checkBlank()
    {
        $cmd = OS::execute("lsblk | grep rom | awk {'print $4'} | sed 's/[^0-9]*//g'");
        $disc_check = OS::execute("wodim -v dev=/dev/sr0 -toc 2>&1");

        if ($cmd < 10 && substr_count($disc_check, "Cannot load media") == 0) {
            return true;
        } else {
            return false;
        }
    }


}