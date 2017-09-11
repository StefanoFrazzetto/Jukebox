<?php

namespace Lib;

use Exception;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Finder\Finder;
use ZipArchive;

class Logger
{
    // TODO read from env file
    const WEB_LOGS = '/logs/';
    const LOGS_DIRECTORY_PATH = '/var/www/html/logs';

    private $finder;

    public function __construct()
    {
        $finder = new Finder();
        $this->finder = $finder->ignoreUnreadableDirs()->in(self::LOGS_DIRECTORY_PATH)->files()->followLinks();
    }

    /**
     * Return the archives in the logs directory.
     *
     * @return Finder the archives in the logs directory.
     */
    public function listArchives()
    {
        $archives = new Finder();
        $archives->ignoreUnreadableDirs()->in(self::LOGS_DIRECTORY_PATH)->files()->followLinks()->name('*.zip');

        return $archives;
    }

    /**
     * Remove archives.
     */
    private function removeOldArchives()
    {
        $archives = self::listArchives();

        if (iterator_count($archives) <= 3) {
            return;
        }

        foreach ($archives as $archive) {
            if (iterator_count($archives) >= 3) {
                unlink($archive);
            }
        }
    }

    /**
     * Return a link to the zip file containing all the log files.
     *
     * @throws RuntimeException if the zip file cannot be created.
     */
    public function download()
    {
        $this->removeOldArchives();

        $logs = self::listLogs();
        $zip = new ZipArchive();
        $filename = date('d_m_Y_gis').'.zip';

        // Try to create the zip file in the logs directory
        if ($zip->open(self::LOGS_DIRECTORY_PATH.'/'.$filename, ZipArchive::CREATE) !== true) {
            throw new RuntimeException('Cannot create zip file');
        }

        foreach ($logs as $log) { // Add the logs
            if (is_readable($log)) {
                $new_filename = substr($log, strrpos($log, '/') + 1);
                $zip->addFile($log, $new_filename);
            }
        }

        if (!$zip->close()) {
            throw new RuntimeException('Cannot close the zip file');
        }

        return $this->listArchives();
    }

    /**
     * List the logs in the directory /var/www/html/logs.
     *
     * // TODO implement symlink creation to /var/log in the install scripts
     *
     * @return Finder
     */
    public function listLogs()
    {
        $finder = $this->finder;
        $finder->name('*.log');

        return $finder;
    }

    /**
     * Clear the specific log file.
     *
     * @param string $filePath the log file path
     *
     * @return bool true on success, false otherwise.
     */
    public static function clearLog($filePath)
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException('The file does not exist.');
        }

        return OS::executeWithResult("echo '' >", $filePath);
    }

    /**
     * Return the content of a log file.
     *
     * @param string $filePath the log file path
     *
     * @throws Exception                if the file path is outside the web dir
     * @throws InvalidArgumentException if the file does not exist
     *
     * @return string the file content
     */
    public static function getLog($filePath)
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException('The file does not exist');
        }

        return file_get_contents($filePath);
    }
}
