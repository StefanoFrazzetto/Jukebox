<?php

require_once __DIR__ . "/Disc.php";

class DiscRipper extends Disc
{
    protected $cdparanoia_log_path;

    protected $lame_log_path;

    protected $destination_dir;

    public function __construct($destination_dir = "")
    {
        parent::__construct();

        $this->destination_dir = $destination_dir;
        $paths = $this->config['ripper'];
        $this->logs_dir = $paths['logs'];
        $this->input_dir = $paths['input'];
        $this->cdparanoia_log_path = $paths['cdparanoia_log'];
        $this->lame_log_path = $paths['lame_log'];
    }

    /**
     * Initializes the attributes needed by the DiscWriter.
     */
    protected function __init()
    {
        $this->total_tracks = OS::execute("cdparanoia -sQ -d $this->device_path |& grep -P -c '^\\s+\\d+\\.' | grep -E '[0-9]'");
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
        return intval($this->total_tracks);
    }

    public function rip()
    {
        if (empty($this->destination_dir)) {
            throw new Exception('You must set an output directory first.');
        }

        if ($this->getStatus() != self::STATUS_IDLE || $this->getStatus() != self::STATUS_COMPLETE) {
            return false;
        }

        $arguments = [
            'device' => $this->device_path,
            'cdparanoia_log_path' => $this->cdparanoia_log_path,
            'lame_log_path' => $this->lame_log_path,
            'ripping_dir' => $this->input_dir,
            'encoding_dir' => $this->destination_dir
        ];

        $pid = OS::executeWithEnv("$this->scripts_dir . rip_handler.sh", $arguments, true);
        $this->setProcessStatusPID(self::STATUS_RIPPING, $pid);
        return true;
    }
}