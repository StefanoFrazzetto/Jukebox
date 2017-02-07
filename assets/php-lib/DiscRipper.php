<?php

require_once __DIR__ . "/Disc.php";

class DiscRipper extends Disc
{
    protected $cdparanoia_log_path;

    protected $lame_log_path;

    protected $handler;

    public function __construct($destination_dir = "")
    {
        parent::__construct();

        $paths = $this->config['ripper'];

        if (!empty($destination_dir)) {
            $this->output_dir = $paths['output'] . "$destination_dir";
        }

        $this->handler = $paths['handler'];
        $this->input_dir = $paths['input'];
        $this->cdparanoia_log_path = $paths['cdparanoia_log'];
        $this->lame_log_path = $paths['lame_log'];
    }

    /**
     * Initializes the attributes needed by the DiscWriter.
     */
    protected function __init()
    {
//        $this->getDiscID();
        $this->getTotalTracks();
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
        if (empty($this->total_tracks)) {
            $this->total_tracks = OS::execute("cdparanoia -sQ -d $this->device_path 2>&1 | grep -P -c '^\\s+\\d+\\.' | grep -E '[0-9]'");
        }
        return intval($this->total_tracks);
    }

    /**
     * Rip a cd
     *
     * @return bool
     * @throws Exception
     */
    public function rip()
    {
        if (empty($this->output_dir)) {
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
            'encoding_dir' => $this->output_dir
        ];

        FileUtil::removeDirectory($this->parent_dir);
        mkdir(dirname($this->cdparanoia_log_path), 0755, true);
        mkdir($this->input_dir, 0755, true);
        mkdir($this->output_dir, 0755, true);

        $pid = OS::executeWithEnv($this->handler, $arguments, true);

        // If it returns false, it means that it was not possible to create the file,
        // therefore the directory was not created, therefore the script did not create
        // the necessary folders.
        return $this->setProcessStatusPID(self::STATUS_RIPPING, $pid) && $pid != 0;
    }
}