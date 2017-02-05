<?php

abstract class DiscStatus
{
    const STATUS_IDLE = 'idle';
    const STATUS_BURNING = 'burning';
    const STATUS_RIPPING = 'ripping';
    const STATUS_ENCODING = 'encoding';
    const STATUS_NORMALIZING = 'normalizing';
    const STATUS_CREATING_IMAGE = 'creating_image';
    const STATUS_FINISHED = 'complete';

//    const MESSAGE_IDLE = 'ready';
//    const MESSAGE_BURNING = 'burning your tracks';
//    const MESSAGE_RIPPING = 'ripping your disc';
//    const MESSAGE_ENCODING = 'encoding the tracks';
//    const MESSAGE_NORMALIZING = 'normalizing';
//    const MESSAGE_CREATING_IMAGE = '';
//    const MESSAGE_FINISHED = '';

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
        $status_file = $this->config['status_file'];

        if (!file_exists($status_file)) {
            $info['status'] = $this->getStatusByProcess();
        } else {
            $info['status'] = json_decode(file_get_contents($status_file), true);
        }

        $this->setInfo($info['status']);
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

            case self::STATUS_FINISHED:
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