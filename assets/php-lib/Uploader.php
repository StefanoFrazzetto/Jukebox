<?php

require_once "Config.php";
require_once "../Providers/MusicBrainz.php";

/**
 * Class Uploader is used to handle the upload of albums into the jukebox.
 */
class Uploader
{
    /** @var string The uploader temp directory */
    private $tmp_path;

    /** @const string The status file name */
    const STATUS_FILE = "uploader_status.json";

    public function __construct()
    {
        $this->tmp_path = self::getPath();
    }

    /**
     * @return string the path to the temp folder
     */
    private static function getPath()
    {
        $config = new Config();
        return $config->get("paths")["uploader"];
    }

    /**
     * @return string JSON that indicates the status of the upload.
     */
    public static function getStatus()
    {
        $config_path = self::getPath();
        return json_encode($config_path . self::STATUS_FILE);
    }

    public function getID3($folder)
    {

    }
}