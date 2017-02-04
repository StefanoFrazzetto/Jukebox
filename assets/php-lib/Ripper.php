<?php

require_once __DIR__ . "/Config.php";
require_once __DIR__ . "/DiscWriter.php";
require_once __DIR__ . "/FileUtil.php";
require_once __DIR__ . "/../Providers/MusicBrainz.php";

class Ripper
{
    /**
     * @var string The ripper scripts path.
     */
    private $scripts_path;

    /**
     * @var string The ripper output path to complete tracks
     */
    private $output_path;

    /**
     * @var string The ripper logs path
     */
    private $logs_path;

    private $disc_writer;

    public function __construct()
    {
        $config = new Config();
        $this->scripts_path = $config->get("path")['scripts']['disc'];
        $this->output_path = $config->get("path")['ripper']['output'];
        $this->logs_path = $config->get("path")['ripper']['logs'];

        if (!file_exists($this->output_path)) {
            mkdir($this->output_path, 0777, true);
        }

        if (!file_exists($this->output_path)) {
            mkdir($this->logs_path, 0777, true);
        }

        $this->disc_writer = new DiscWriter();
    }

    /**
     * Removes both the logs and the output directory.
     */
    public function clean()
    {
        FileUtil::removeDirectory($this->logs_path);
        FileUtil::removeDirectory($this->output_path);
    }

    /**
     * Starts the ripping process.
     */
    public function start()
    {
        $this->disc_writer->ripDisc($this->output_path, $this->logs_path);
    }

    public function getStatus()
    {
        // TODO
    }

}