<?php

namespace Lib;

use Exception;
use InvalidArgumentException;

/**
 * Class DiscRipper provides access to the disc device to
 * rip and encode tracks from a CD/DVD.
 *
 * @author Stefano Frazzetto <https://github.com/StefanoFrazzetto>
 */
class DiscRipper extends Disc
{
    /** @var string the path to cdparanoia log file */
    protected $cdparanoia_log_path;

    /** @var string the path to lame log file */
    protected $lame_log_path;

    /** @var string the path to the file that handles the ripping */
    protected $handler;

    /** @var string the directory where the files will be moved */
    protected $destination_dir;

    /** @var string the id for the current uploader session */
    private $uploader_id;

    public function __construct($uploader_id = '', $cd = null)
    {
        parent::__construct();
        if (empty($cd)) {
            $cd = 1;
        } else if (!is_int($cd) || $cd < 1) {
            throw new Exception("The parameter passes for the cd ($cd) is not valid.");
        }

        if (!empty($uploader_id)) {
            $this->uploader_id = $uploader_id;
            $path = Config::getPath('uploader') . $uploader_id . "/CD$cd";

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $this->destination_dir = $path;
        }
    }

    /**
     * Return the discid for this CD/DVD.
     *
     * @return string The discid of this CD/DVD.
     */
    public function getDiscID()
    {
        if (empty($this->disc_id)) {
            $this->disc_id = OS::execute("discid $this->device_path");
        }

        return $this->disc_id;
    }

    /**
     * Start ripping a CD/DVD.
     *
     * @throws Exception if no directory was passed to the constructor.
     *
     * @return bool true on success, false if it was not possible to start
     *              the ripping process.
     */
    public function rip()
    {
        if (empty($this->destination_dir)) {
            throw new Exception('The output directory was not specified.');
        }

        if ($this->getStatus() != self::STATUS_IDLE) {
            return false;
        }

        $arguments = [
            'device'              => $this->device_path,
            'cdparanoia_log_path' => $this->cdparanoia_log_path,
            'lame_log_path'       => $this->lame_log_path,
            'ripping_dir'         => $this->input_dir,
            'encoding_dir'        => $this->destination_dir
        ];

        FileUtils::remove(self::getParentPath(), true);
        mkdir(dirname($this->cdparanoia_log_path), 0755, true);
        mkdir($this->input_dir, 0755, true);

        // Set the total tracks before starting the process.
        $process['total_tracks'] = $this->getTotalTracks();

        $pid = OS::executeWithEnv($this->handler, $arguments, true);

        $process['uploader_id'] = $this->uploader_id;
        $process['status'] = self::STATUS_RIPPING;
        $process['destination_dir'] = $this->destination_dir;
        $process['pid'] = $pid;

        // If it returns false, it means that it was not possible to create the file,
        // therefore the directory was not created, therefore the script did not create
        // the necessary folders.
        return $this->createStatusFile($process) && $pid != 0;
    }

    /**
     * Returns the path where the ripper directories are saved.
     *
     * @return string the path where the ripper directories are saved.
     */
    public static function getParentPath()
    {
        $config = new Config();
        return $config->get('disc')['ripper']['parent'];
    }

    /**
     * Return the number of tracks on the CD/DVD.
     *
     * @return int The number of tracks on the CD/DVD.
     */
    public function getTotalTracks()
    {
        if (empty($this->total_tracks) && file_exists($this->status_file)) {
            $status_file_content = $this->getStatusFileContent();
            $total_tracks = $status_file_content['total_tracks'];
        }

        if (!isset($total_tracks)) {
            $total_tracks = OS::execute("cdparanoia -sQ -d $this->device_path 2>&1 | grep -P -c '^\\s+\\d+\\.' | grep -E '[0-9]'");
            // TODO save into status file
        }

        $this->total_tracks = intval($total_tracks);

        return intval($this->total_tracks);
    }

    /**
     * Stop the ripping process.
     *
     * @return bool true on success, false otherwise.
     */
    public function stop()
    {
        if (!file_exists($this->status_file)) {
            return false;
        }

        $content = $this->getStatusFileContent();
        $pid = $content['pid'];

        $process = new Process();
        try {
            $process->setPid($pid);
            $process->stop();
        } catch (InvalidArgumentException $x) {}

        self::reset();

        return true;
    }

    /**
     * Removes all the directories and files user for the process.
     */
    public function reset()
    {
        // Remove all the directories for the ripper
        $parent = self::getParentPath();
        if (file_exists(self::getParentPath())) {
            FileUtils::remove($parent, true);
        }

        if (isset($this->uploader_id)) {
            FileUtils::remove(self::getDestinationPath(), true);
        }
    }

    private function getDestinationPath()
    {
        $status_file = $this->getStatusFileContent();
        return $status_file['destination_dir'];
    }

    /**
     * Initializes the attributes needed by the DiscWriter.
     */
    protected function __init()
    {
        $paths = $this->config['ripper'];

        $this->handler = $paths['handler'];
        $this->input_dir = $paths['input'];
        $this->cdparanoia_log_path = $paths['cdparanoia_log'];
        $this->lame_log_path = $paths['lame_log'];
    }

    /**
     * Set the exact status and percentage for the ripper.
     * <b>This method will be invoked only if the process is still running!</b>.
     */
    protected function updateStatus()
    {
        $status = $this->getStatusByProcess();
        $total_tracks = $this->getTotalTracks();
        $percentage = 0;
        $message = '';

        $encoded = $this->getEncodedTracks();
        $ripped = $this->getRippedTracks();

        $content = self::getStatusFileContent();
        $pid = intval($content['pid']);
        $process = new Process();
        try {
            $process->setPid($pid);
        } catch (InvalidArgumentException $x) {}
        $process_status = $process->status();

        // Calculate the process percentage
        if ($ripped != 0 && $process_status) {
            $status = self::STATUS_RIPPING;
            $average = floor(($ripped + $encoded) / 2);
            $percentage = $this->calculatePercentage($average, $total_tracks);
        }

        if ($encoded == $total_tracks && $total_tracks > 0) {
            $status = self::STATUS_COMPLETE;
            $message = 'your disc is ready';
            $percentage = 100;
        }

        $this->setStatusMessagePercentage($status, $message, $percentage);
    }

    /**
     * Checks if the processes used the ripper are currently running
     * and returns the status associated with the running process.
     *
     * @return string The status associated with the running process.
     */
    protected function getStatusByProcess()
    {
        if (OS::isProcessRunning('cdparanoia')) {
            $status = self::STATUS_RIPPING;
        } elseif (OS::isProcessRunning('lame')) {
            $status = self::STATUS_ENCODING;
        } else {
            $status = self::STATUS_IDLE;
        }

        return $status;
    }

    /**
     * Return the number of encoded tracks.
     *
     * @return int the number of encoded tracks.
     */
    public function getEncodedTracks()
    {
        $path = $this->getDestinationPath();

        return FileUtils::countFiles($path);
    }

    /**
     * Return the number of ripped tracks.
     *
     * @return int the number of ripped tracks.
     */
    public function getRippedTracks()
    {
        $path = self::getInputPath();

        return FileUtils::countFiles($path);
    }

    /**
     * Returns the path where the ripped tracks are saved.
     *
     * @return string the path where the ripped tracks are saved.
     */
    public static function getInputPath()
    {
        $config = new Config();
        return $config->get('disc')['ripper']['input'];
    }

    /**
     * Return the percentage using 2 values.
     *
     * @param int|float $val1 the partial value
     * @param int|float $val2 the total value
     *
     * @return int the percentage obtained from val1/val2
     */
    private function calculatePercentage($val1, $val2)
    {
        if ($val1 == 0 || $val2 == 0) {
            return 0;
        }

        return intval(round(($val1 / $val2) * 100));
    }
}
