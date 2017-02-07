<?php

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/DiscStatus.php';
require_once __DIR__ . '/OS.php';

/**
 * Class DiscWriter handles the disc writer operations...
 *
 * @author Stefano Frazzetto <https://github.com/StefanoFrazzetto>
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

    /** @var  string The directory containing the scripts */
    protected $scripts_dir;

    /** @var  string The directory where the logs are saved. */
    protected $logs_dir;

    /** @var string The directory where the input files are saved. */
    protected $input_dir;

    /** @var string The directory where the output files are saved. */
    protected $output_dir;

    /** @var  string The directory where the ready to use files are saved. */
    protected $destination_dir;

    /** @var  string The directory containing the disc ripper/burner sub directories */
    protected $parent_dir;

    public function __construct()
    {
        parent::__construct();

        $this->parent_dir = $this->config['parent_dir'];
        $this->scripts_dir = $this->config['scripts'];
        $this->logs_dir = $this->config['logs'];

        $device_name = OS::getDevicesByType("rom");
        $this->device_path = "/dev/$device_name";

        if ($this->getStatus() == self::STATUS_IDLE) {
            $this->__init();
        }
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