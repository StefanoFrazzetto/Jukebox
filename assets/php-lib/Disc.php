<?php

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/OS.php';

/**
 * Class DiscWriter handles the disc writer operations...
 *
 * @author Stefano Frazzetto <https://github.com/StefanoFrazzetto>
 * @version 1.0.0
 */
abstract class Disc
{
    const STATUS_IDLE = 'idle';
    const STATUS_BURNING = 'burning';
    const STATUS_RIPPING = 'ripping';
    const STATUS_ENCODING = 'encoding';
    const STATUS_NORMALIZING = 'normalizing';
    const STATUS_CREATING_IMAGE = 'creating_image';
    const STATUS_COMPLETE = 'complete';
    const STATUS_NO_DISC = 'no_disc';
    const STATUS_BUSY = 'busy';

    /** @var array The configuration variables for the disc device */
    protected $config;

    /** @var  string The path to the file containing the device status */
    protected $status_file;

    /** @var string The current status */
    protected $status;

    /** @var string The message relative to the device status */
    protected $message = "";

    /** @var int The percentage relative to the current operation */
    protected $percentage = 0;

    /** @var bool Indicates if the process is complete or not */
    protected $complete = false;

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

    /** @var  int The process id */
    protected $pid;

    public function __construct()
    {
        $config = new Config();
        $this->config = $config->get('disc');
        $this->status_file = $this->config['status_file'];

        $this->parent_dir = $this->config['parent_dir'];
        $this->scripts_dir = $this->config['scripts'];
        $this->logs_dir = $this->config['logs'];

        $device_name = OS::getDevicesByType("rom");
        $this->device_path = "/dev/$device_name";


        if (!file_exists($this->status_file)) {
            $this->status = self::STATUS_IDLE;
        } else {
            // Set status, message, and percentage.
            $this->setCurrentStatus();
        }

        if ($this->getStatus() == self::STATUS_IDLE) {
            $this->__init();
        }
    }

    abstract protected function __init();


    abstract protected function updateStatus();

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
     * TODO
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
     * TODO
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

    /**
     * TODO
     * @return bool
     */
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

    protected function setStatus($status)
    {
        $this->status = $status;
    }

    protected function setMessage($message)
    {
        $this->message = $message;
    }

    protected function setPercentage($percentage)
    {
        $this->percentage = $percentage;
    }

    protected function getStatusFileContent()
    {
        $file_content = file_get_contents($this->status_file);
        return json_decode($file_content, true);
    }

    protected function setStatusMessagePercentage($status, $message, $percentage)
    {
        $this->setStatus($status);
        $this->setMessage($message);
        $this->setPercentage($percentage);
    }

    /**
     * Return the current status of the disc device.
     *
     * If a process id related to ripping or burning exists,
     * the specific status associated with the current operation is
     * returned, otherwise it means that the process is complete.
     */
    protected function setCurrentStatus()
    {
        if (!file_exists($this->status_file)) {
            $this->status = self::STATUS_IDLE;
        }

        $content = self::getStatusFileContent();

        $pid = intval($content['pid']);
        $process = new Process();
        $process->setPid($pid);

        // If the process is running, get the specific status
        // calculate percentage, and set a message.

        if (!$process->status()) {
            // Process complete
            $this->setStatusMessagePercentage(self::STATUS_COMPLETE, 'process complete', 100);
            return;
        }

        $this->updateStatus();
    }

    protected function createStatusFile($parameters = "")
    {
        $info = [];
        if (!empty($parameters) && is_array($parameters)) {
            foreach ($parameters as $key => $value) {
                $info[$key] = $value;
            }
        }

        // Create the directory if it does not exist
        $dir = dirname($this->status_file);
        if (!file_exists($dir)) {
            $dircr = mkdir($dir, 0777, true);
            if (!$dircr) {
                return false;
            }
        }

        return FileUtils::createFile($this->status_file, $info, false, true);
    }

    /**
     * Returns the current status of the disc device
     *
     * @return string The status of the device.
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Returns the message relative to the process that is
     * being executed by the disc device.
     *
     * @return string The message relative to the process, if any.
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns the percentage relative to the process that is
     * being executed by the disc device.
     *
     * @return string The percentage relative to the process, if any.
     */
    public function getPercentage()
    {
        return $this->percentage;
    }


}