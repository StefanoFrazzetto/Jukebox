<?php

require_once __DIR__ . "/Disc.php";

/**
 * Class DiscRipper provides access to the disc device to
 * rip and encode tracks from a CD/DVD.
 *
 * @author Stefano Frazzetto <https://github.com/StefanoFrazzetto>
 */
class DiscRipper extends Disc
{
    /** @var  string the path to cdparanoia log file */
    protected $cdparanoia_log_path;

    /** @var  string the path to lame log file */
    protected $lame_log_path;

    /** @var  string the path to the files that handles the ripping */
    protected $handler;

    /** @var string the directory where the files will be moved */
    protected $final_dir;

    public function __construct($final_dir_name = "")
    {
        parent::__construct();
        if (!empty($final_dir_name)) {
            $this->final_dir = Config::getPath('uploader') . $final_dir_name;
        }
    }

    /**
     * Initializes the attributes needed by the DiscWriter.
     */
    protected function __init()
    {
        $paths = $this->config['ripper'];

        $this->handler = $paths['handler'];
        $this->input_dir = $paths['input'];
        $this->output_dir = $paths['output'];
        $this->cdparanoia_log_path = $paths['cdparanoia_log'];
        $this->lame_log_path = $paths['lame_log'];
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
     * Start ripping a CD/DVD.
     *
     * @return bool true on success, false if it was not possible to start
     * the ripping process.
     * @throws Exception if no directory was passed to the constructor.
     */
    public function rip()
    {
        if (empty($this->final_dir)) {
            throw new Exception('You must set an output directory first.');
        }

        if ($this->getStatus() != self::STATUS_IDLE && $this->getStatus() != self::STATUS_COMPLETE) {
            return false;
        }

        $arguments = [
            'device' => $this->device_path,
            'cdparanoia_log_path' => $this->cdparanoia_log_path,
            'lame_log_path' => $this->lame_log_path,
            'ripping_dir' => $this->input_dir,
            'encoding_dir' => $this->output_dir,
            'final_dir' => $this->final_dir
        ];

        FileUtils::remove($this->parent_dir, true);
        mkdir(dirname($this->cdparanoia_log_path), 0755, true);
        mkdir($this->input_dir, 0755, true);
        mkdir($this->output_dir, 0755, true);
//        mkdir($this->final_dir, 0755, true);

        // Set the total tracks before starting the process.
        $process['total_tracks'] = $this->getTotalTracks();

        $pid = OS::executeWithEnv($this->handler, $arguments, true);

        $process['status'] = self::STATUS_RIPPING;
        $process['pid'] = $pid;

        // If it returns false, it means that it was not possible to create the file,
        // therefore the directory was not created, therefore the script did not create
        // the necessary folders.
        return $this->createStatusFile($process) && $pid != 0;
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
        $process->setPid($pid);
        return $process->stop();
    }

    /**
     * Return the percentage using 2 values.
     *
     * @param int|float $val1 the partial value
     * @param int|float $val2 the total value
     * @return int the percentage obtained from val1/val2
     */
    private function calculatePercentage($val1, $val2)
    {
        if ($val1 == 0 || $val2 == 0) {
            return 0;
        }

        return intval(round(($val1 / $val2) * 100));
    }

    /**
     * Checks if the processes used the ripper are currently running
     * and returns the status associated with the running process.
     *
     * @return string The status associated with the running process.
     */
    protected function getStatusByProcess()
    {
        if (OS::isProcessRunning("cdparanoia")) {
            $status = self::STATUS_RIPPING;
        } elseif (OS::isProcessRunning("lame")) {
            $status = self::STATUS_ENCODING;
        } else {
            $status = self::STATUS_IDLE;
        }

        return $status;
    }

    /**
     * Return the number of ripped tracks.
     *
     * @return int the number of ripped tracks.
     */
    public function getRippedTracks()
    {
        $config = new Config();
        $path = $config->get('disc')['ripper']['input'];
        return FileUtils::countFiles($path);
    }

    /**
     * Return the number of encoded tracks.
     *
     * @return int the number of encoded tracks.
     */
    public function getEncodedTracks()
    {
        $config = new Config();
        $path = $config->get('disc')['ripper']['output'];
        return FileUtils::countFiles($path);
    }

    /**
     * Set the exact status and percentage for the ripper.
     * <b>This method will be invoked only if the process is still running!</b>
     */
    protected function updateStatus()
    {
        $status = $this->getStatusByProcess();
        $total_tracks = $this->getTotalTracks();
        $percentage = 0;
        $message = "";

        $encoded = $this->getEncodedTracks();
        $ripped = $this->getRippedTracks();

        // Calculate the process percentage
        if ($ripped != 0) {
            $average = floor(($ripped + $encoded) / 2);
            $percentage = $this->calculatePercentage($average, $total_tracks);
        }

        if ($encoded == $total_tracks) {
            $status = self::STATUS_COMPLETE;
            $message = "your disc is ready";
            $percentage = 100;
        }
        $this->setStatusMessagePercentage($status, $message, $percentage);
        return;
    }
}