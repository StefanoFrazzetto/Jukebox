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
 * Class Zipper.
 *
 * @author Stefano Frazzetto
 *
 * @version 2.1.0
 */
class Zipper
{
    /**
     * @var string the path to the album directory
     */
    private $albumDirectoryPath;

    /**
     * @var string the path to the directory where the zipped file will be saved
     */
    private static $DOWNLOADS_DIRECTORY;

    /**
     * @var string the absolute path to the zip lock file
     */
    private static $ZIP_LOCK;

    /**
     * @var int the time after which an album must be zipped again (1 day)
     */
    private static $FILE_EXPIRE_TIME = 86400;

    /**
     * @var int the id of the album to zip
     */
    private $albumID;

    /**
     * @var string the zip file name
     */
    private $outputFilePath;

    /**
     * Zipper constructor.
     *
     * @param int $albumID the album id
     *
     * @throws Exception
     */
    public function __construct($albumID)
    {
        // Check if parameters are empty
        if (empty($albumID)) {
            throw new InvalidArgumentException('The album ID cannot be empty.');
        }

        $config = new Config();
        $paths = $config->get('paths');

        $album_path = $paths['albums_root'].$albumID;
        $downloads_directory = $paths['downloads_directory'];

        // Check if the album directory exist
        if (!file_exists($album_path)) {
            error_log("Trying to zip non existent album with ID $albumID");

            throw new InvalidArgumentException('Error. The album does not exist.');
        }

        // Create the downloads directory if does not exist
        if (!file_exists($downloads_directory)) {
            mkdir($downloads_directory, 0744, true);
        }

        $this->albumDirectoryPath = realpath($album_path).'/';
        self::$DOWNLOADS_DIRECTORY = realpath($downloads_directory).'/';
        self::$ZIP_LOCK = self::$DOWNLOADS_DIRECTORY.'zip_check';

        // Create the file name
        $Album = Album::getAlbum($albumID);
        $title = $Album->getTitle();
        $artists = $Album->getArtistsName();
        $outputFileName = preg_replace('/[^A-Za-z0-9\-]/', '_', implode($artists, '-')).
            '-'.preg_replace('/[^A-Za-z0-9\-]/', '_', $title).'.zip';

        $this->albumID = intval($albumID);
        $this->outputFilePath = self::$DOWNLOADS_DIRECTORY.$outputFileName;
    }

    /**
     * @return int the size of the album directory.
     */
    public function getAlbumSize()
    {
        return FileUtils::getDirectorySize($this->albumDirectoryPath);
    }

    /**
     * @return int the percentage of the process.
     */
    public function getProgressPercentage()
    {
        try {
            $album_dir_size = FileUtils::getDirectorySize($this->albumDirectoryPath);
            $zip_file_size = FileUtils::getFileSize($this->outputFilePath);
        } catch (Exception $e) {
            return 0;
        }

        if (empty($album_dir_size) || empty($zip_file_size)) {
            return 0;
        }

        return (int) floor($album_dir_size / $zip_file_size) * 100;
    }

    /**
     * Remove old files from the download directory.
     */
    private function removeOldFiles()
    {
        FileUtils::deleteFilesOlderThan(self::$DOWNLOADS_DIRECTORY, self::$FILE_EXPIRE_TIME);
    }

    /**
     * Create the zipped file.
     *
     * @throws Exception if the file cannot be created.
     *
     * @return bool|string true on success, otherwise an error explaining the cause of the problem.
     */
    public function createZip()
    {
        $this->removeOldFiles();

        // If the file already exists
        if (file_exists($this->outputFilePath)) {
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
        if ($zip->open($this->outputFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Cannot not create zip archive for album ID: $this->albumID");
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->albumDirectoryPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            // Skip directories$name =>  (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $file_path = $file->getRealPath();
                $relative_path = substr($file_path, strlen($this->albumDirectoryPath));

                // Add current file to archive
                $zip->addFile($file_path, $relative_path);
            }
        }

        // Finalize the zip file and removes the lock

        return $zip->close() && $this->unlockZip() ?: "Cannot not close zip file. Album ID: $this->albumID";
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
    public function lockZip()
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
        if (!file_exists($this->outputFilePath)) {
            throw new LogicException('The zip file does not exist');
        }

        $relative_file_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->outputFilePath);

        return 'http://'.$_SERVER['HTTP_HOST'].$relative_file_path;
    }
}
