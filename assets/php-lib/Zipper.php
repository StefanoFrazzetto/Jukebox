<?php

namespace Lib;


use Exception;
use InvalidArgumentException;
use Lib\MusicClasses\Album;
use LogicException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

/**
 * Class Zipper
 *
 * @package Lib
 * @author Stefano Frazzetto
 * @version 2.0.0
 */
class Zipper
{
    /**
     * @var string the path to the album directory
     */
    private static $ALBUM_DIRECTORY;

    /**
     * @var string the path to the directory where the zipped file will be saved
     */
    private static $DOWNLOADS_DIRECTORY;
    /**
     * @var string $ZIP_LOCK the absolute path to the zip lock file
     */
    private static $ZIP_LOCK;
    /**
     * @var int $albumID the id of the album to zip
     */
    private $albumID;
    /**
     * @var string $outputFileName the zip file name
     */
    private $outputFileName;

    /**
     * Zipper constructor.
     * @param int $albumID the album id
     *
     * @throws InvalidArgumentException if the album does not exist.
     */
    public function __construct($albumID)
    {
        // Check if parameters are empty
        if (empty($albumID)) {
            throw new InvalidArgumentException('The album ID cannot be empty.');
        }

        $config = new Config();
        $paths = $config->get('paths');

        self::$ALBUM_DIRECTORY = $paths['albums_root'] . $albumID;
        self::$DOWNLOADS_DIRECTORY = $paths['downloads_directory'];
        self::$ZIP_LOCK = self::$DOWNLOADS_DIRECTORY . 'zip_check';

        // Check if the album directory exists
        if (!file_exists(self::$ALBUM_DIRECTORY)) {
            throw new InvalidArgumentException('Error. The album does not exist.');
        }

        // Create the parent directory if does not exist
        if (!file_exists(self::$DOWNLOADS_DIRECTORY)) {
            mkdir(self::$DOWNLOADS_DIRECTORY);
        }

        // Create the file name
        $this->albumID = intval($albumID);
        $Album = Album::getAlbum($albumID);
        $title = $Album->getTitle();
        $artists = $Album->getArtistsName();

        $this->outputFileName = preg_replace('/[^A-Za-z0-9\-]/', '_', implode($artists, '-')) .
            '-' . preg_replace('/[^A-Za-z0-9\-]/', '_', $title) . '.zip';
    }

    /**
     * Create the zipped file.
     *
     * @return bool|string true on success, otherwise an error explaining the cause of the problem.
     *
     * @throws Exception if the file cannot be created.
     */
    public function createZip()
    {
        $output_file_path = self::$DOWNLOADS_DIRECTORY . $this->outputFileName;

        // If the file already exists
        if (file_exists($output_file_path)) {
            return true;
        }

        // Check if is already zipping an album
        if (!$this->canZip()) {
            return 'Error. The system is already creating a zip file.';
        }

        // Create the lock file
        if (!$this->lockZip()) {
            throw new Exception('Cannot create lock file. Please try again later.');
        }

        $zip = new ZipArchive();
        if ($zip->open($output_file_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Cannot not create zip archive for album ID: $this->albumID");
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::$ALBUM_DIRECTORY),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $file_path = $file->getRealPath();
                $relative_path = substr($file_path, strlen(self::$ALBUM_DIRECTORY) + 1);

                // Add current file to archive
                $zip->addFile($file_path, $relative_path);
            }
        }

        // Remove the lock file
        $this->unlockZip();

        return $zip->close() ?: "Cannot not close zip file. Album ID: $this->albumID";
    }

    /**
     * Check if the zipping process can be started.
     *
     * The method checks if the zip check file exists. If it does and was created less
     * than 5 minutes ago, then the process cannot be started. If the file has existed
     * for more than 5 minutes, it is safe to assume that the previous process has been
     * completed already, so we can start a new one.
     *
     * @return bool true if the process can be started, otherwise false.
     */
    private function canZip()
    {
        if (file_exists(self::$ZIP_LOCK)) {
            return (time() - file_get_contents(self::$ZIP_LOCK)) > 300 && $this->unlockZip();
        }

        return true;
    }

    /**
     * Remove the zip lock file.
     *
     * @return bool true if the file was removed, otherwise false.
     */
    private function unlockZip()
    {
        return file_exists(self::$ZIP_LOCK) && unlink(self::$ZIP_LOCK);
    }

    /**
     * Create a zip lock file to avoid overlapping processes.
     *
     * @return bool true if the file was created, otherwise false.
     */
    private function lockZip()
    {
        return file_put_contents(self::$ZIP_LOCK, time()) !== false;
    }

    /**
     * Return the URL to the zipped file.
     *
     * @return string the download URL.
     */
    public function getDownloadURL()
    {
        $absolute_file_path = self::$DOWNLOADS_DIRECTORY . $this->outputFileName;
        if (!file_exists($absolute_file_path)) {
            throw new LogicException('The zip file does not exist');
        }

        $relative_file_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $absolute_file_path);

        return 'http://' . $_SERVER['HTTP_HOST'] . $relative_file_path;
    }
}
