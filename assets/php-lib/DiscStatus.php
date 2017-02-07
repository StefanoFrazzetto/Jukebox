<?php

require_once __DIR__ . '/FileUtil.php';
require_once __DIR__ . '/Process.php';

abstract class DiscStatus
{
    const STATUS_IDLE = 'idle';
    const STATUS_BURNING = 'burning';
    const STATUS_RIPPING = 'ripping';
    const STATUS_ENCODING = 'encoding';
    const STATUS_NORMALIZING = 'normalizing';
    const STATUS_CREATING_IMAGE = 'creating_image';
    const STATUS_COMPLETE = 'complete';

    /** @var array The configuration variables for the disc device */
    protected $config;

    /** @var string The current status */
    protected $status;

    /** @var string The message relative to the device status */
    protected $message;

    /** @var int The percentage relative to the current operation */
    protected $percentage;

    /** @var boolean The flag indicating if another disc is needed */
    protected $next_cd;

    /** @var  string The path to the file containing the device status */
    protected $status_file;

    /**
     * DiscStatus constructor loads the configuration parameter and looks
     * for the disc device status. If the file containing the current status,
     * it checks if any process related to the disc device operations is running
     * and stores the correspondent status.
     */
    public function __construct()
    {
        $config = new Config();
        $this->config = $config->get('disc');
        $this->status_file = $this->config['status_file'];

        if (file_exists($this->status_file)) {
            $status = $this->getStatusFromStatusFile();
        } else {
            $status = $this->getStatusByProcess();
        }

        $this->setInfo($status);
    }

    /**
     * Return the current status of the disc device.
     *
     * If a process id related to ripping or burning exists,
     * the specific status associated with the current operation is
     * returned, otherwise it means that the process is complete.
     *
     * @return string The disc status.
     */
    private function getStatusFromStatusFile()
    {
        $file_content = file_get_contents($this->status_file);
        $content = json_decode($file_content, true);

        $pid = intval($content['pid']);
        $process = new Process();
        $process->setPid($pid);

        // If the process is running, get the specific status
        if ($process->status()) {
            return $content['status'];
        } else {
            // Remove the status file
            unlink($this->status_file);
        }

        return self::STATUS_COMPLETE;
    }

    protected function setProcessStatusPID($status, $pid)
    {
        $info['status'] = $status;
        $info['pid'] = $pid;

        // Create the directory if it does not exist
        $dir = dirname($this->status_file);
        if (!file_exists($dir)) {
            $dircr = mkdir($dir, 0777, true);
            echo "Trying to create dir: " . var_dump($dircr);
        }

        return FileUtil::createFile($this->status_file, $info, false, true);
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

    /**
     * Returns a boolean that indicates if the process requires another
     * disc in order to proceed.
     *
     * @return boolean true if another disc is needed by the current
     * process, such as burning a compilation.
     */
    public function hasNextCD()
    {
        return $this->next_cd;
    }

    /**
     * Checks if the processes used by the burner or the ripper
     * are currently running and returns the status associated
     * with the running process.
     *
     * @return string The status associated with the running process.
     */
    protected function getStatusByProcess()
    {
        if (OS::isProcessRunning("lame")) {

            $status = self::STATUS_ENCODING;

        } elseif (OS::isProcessRunning("mkisofs") || OS::isProcessRunning("genisoimage")) {

            $status = self::STATUS_CREATING_IMAGE;

        } elseif (OS::isProcessRunning("normalize-audio")) {

            $status = self::STATUS_NORMALIZING;

        } elseif (OS::isProcessRunning("wodim")) {

            $status = self::STATUS_BURNING;

        } else {

            $status = self::STATUS_IDLE;
        }

        return $status;
    }

    /**
     * Sets both message and percentage according to the device status.
     *
     * @param string $status The status of the device
     */
    private function setInfo($status)
    {
        $this->status = $status;
        $this->message = "";
        $this->percentage = 0;

        switch (self::getStatus()) {

            case self::STATUS_IDLE:
                $this->setPercentage(0);
                break;

            case self::STATUS_ENCODING:
                $this->setPercentage(20);
                $this->decoding();
                break;

            case self::STATUS_NORMALIZING:
                $this->setPercentage(40);
                $this->normalizing();
                break;

            case self::STATUS_CREATING_IMAGE:
                $this->setPercentage(65);
                $this->creatingISO();
                break;

            case self::STATUS_BURNING:
                $this->burning();
                self::setPercentage(75);
                break;

            case self::STATUS_COMPLETE:
                $this->message = "your disc is ready";
                self::setPercentage(100);

                // Check if there's something else to burn.
                if ($this->next_cd == true) {
                    $this->message = "insert the next disc";
                }
                break;

            default:
                $this->message = "general error";
        }
    }

    private function setPercentage($p)
    {
        $this->percentage = $p;
    }

    private function decoding()
    {
//        $handle = fopen(self::$_decode_output, "r");
//        if ($handle) {
//            while (($line = fgets($handle)) !== false) {
//                if (strpos($line, 'input:'))
//                    break;
//            }
//
//            $line = str_replace("\t", '', $line); // remove tabs
//            $line = str_replace("\n", '', $line); // remove new lines
//            $line = str_replace("\r", '', $line); // remove carriage returns
//            $line = basename($line);
//        } else {
//            $line = "undefined error";
//        }
//
//        fclose($handle);
//
//        return $line;
    }

    private function normalizing()
    {
    }

    private function creatingISO()
    {
    }

    private function burning()
    {
//        $file_content = file_get_contents(self::$_burn_output);
//
//        $tracks_count = substr_count($file_content, "Track");
//        $total = FileUtil::countFiles(BurnerHandler::$_burner_folder, "mp3");
//
//        $this->partial_progress = $this->percentage($tracks_count, $total);
    }
}