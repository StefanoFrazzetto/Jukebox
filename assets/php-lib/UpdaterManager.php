<?php

namespace Lib;

use Exception;
use Symfony\Component\Finder\Finder;

/**
 * Class UpdaterManager handles the software updates using the update files
 * provided in the updater directory.
 *
 * // TODO: use another database.
 */
class UpdaterManager
{
    /** @var */
    const UPDATER_TABLE_NAME = 'updaterlog';
    /** @var */
    private static $UPDATER_FILE_NAME_PATTERN = '/^\d+_([\w_]+).json/i';
    /** @var string the directory containing the migrations */
    private $updatesDirectory;

    /** @var bool $errors */
    private $errors = false;

    /** @var array $updates */
    private $updates;

    /** @var bool $debugMode true if running automated tests */
    private $debugMode = false;

    public function __construct($debug_mode = false)
    {
        $this->debugMode = $debug_mode;

        try {
            $this->setup();
            $this->updates = $this->getUpdateFiles();
            $this->removeExecutedUpdates();
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->errors = true;
        }
    }

    /**
     * Set up the class.
     *
     * @throws Exception
     */
    private function setup()
    {
        $this->updatesDirectory = static::getUpdatesDirectory();

        $database = new Database();
        if (!$database->tableExists(static::UPDATER_TABLE_NAME)) {
            throw new Exception('The updater table does not exist');
        }
    }

    /**
     * Return the directory containing the update files.
     *
     * @return string
     */
    public static function getUpdatesDirectory()
    {
        return __DIR__.'/../../updater/';
    }

    /**
     * Retrieve the update files.
     *
     * @throws Exception
     *
     * @return array if duplicate files are found
     */
    private function getUpdateFiles()
    {
        $finder = new Finder();
        $files = $finder->in($this->updatesDirectory)->files();

        $updates = [];
        foreach ($files as $updaterFilePath) {
            if (self::isValidMigrationFileName($updaterFilePath)) {
                $version = UpdaterFile::getVersionFromName(basename($updaterFilePath));

                // Check for duplicate updates
                if (isset($updates[$version])) {
                    throw new Exception('Updater: duplicate file found');
                }

                $updates[$version] = $updaterFilePath;
            }
        }

        ksort($updates);

        return $updates;
    }

    /**
     * Check if an update file file name is valid.
     *
     * @param string $file_name file name
     *
     * @return bool
     */
    public static function isValidMigrationFileName($file_name)
    {
        $matches = [];

        return preg_match(static::$UPDATER_FILE_NAME_PATTERN, basename($file_name), $matches);
    }

    /**
     * Remove the already executed updates from the array.
     */
    private function removeExecutedUpdates()
    {
        $database = new Database();
        $updates = $database->select('version', static::UPDATER_TABLE_NAME);

        foreach ($updates as $update) {
            $version = $update->version;

            if (isset($this->updates[$version])) {
                unset($this->updates[$version]);
            }
        }
    }

    /**
     * Run all the updates.
     *
     * Pulls from git, runs the database migrations, and updates the system.
     *
     * @return array containing success (bool) and a message (string) containing the errors
     */
    public function run()
    {
        $success = false;
        $messages = '';

        // GIT
        $git = new Git();
        $git_res = $git->pull(null, true);
        if (!$git_res) {
            $messages .= 'Failed to retrieve the updates';
        }

        // Database
        $database = new Database();
        $db_res = $database->migrate();
        if (!$db_res) {
            $messages .= ' Failed to run the database migrations';
        }

        // System
        if ($this->hasErrors()) {
            $messages .= ' Failed to run the system updater';
        } else {
            $sys_res = $this->runSystemUpdates();
            $success = $git_res && $db_res && $sys_res;
        }

        if (!$success) {
            error_log("Failed to run the updater: $messages");
        }

        return ['status' => $success, 'message' => $messages];
    }

    /**
     * Check if there were errors during the initialization.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return $this->errors;
    }

    /**
     * Run the updates.
     *
     * @throws Exception if the updaters is not ready
     */
    public function runSystemUpdates()
    {
        if ($this->errors) {
            throw new Exception('Cannot run the updater');
        }

        // No updates
        if (empty($this->updates)) {
            return true;
        }

        $ret = false;
        foreach ($this->updates as $version => $file_path) {
            $update = new UpdaterFile($file_path);
            if ($update->isValid()) {
                $update->execute(); // execute the update if it's valid

                if ($update->wasSuccessful()) {
                    // Add the update to the database only if not in debug mode
                    $ret = !$this->debugMode ? $this->addToDatabase($update) : true;
                } else {
                    $this->errors = true;
                    break;
                }
            }
        }

        return $ret;
    }

    /**
     * Add the update to the database.
     *
     * @param UpdaterFile $updaterFile
     *
     * @throws Exception
     *
     * @return bool
     */
    private function addToDatabase(UpdaterFile $updaterFile)
    {
        if (empty($updaterFile)) {
            throw new Exception('The parameter cannot be empty');
        }

        $database = new Database();
        $data = [
            'version'    => $updaterFile->getVersion(),
            'file_name'  => $updaterFile->getFileName(),
            'start_time' => $updaterFile->getStartTime(),
            'end_time'   => $updaterFile->getEndTime(),
        ];

        return $database->insert(static::UPDATER_TABLE_NAME, $data);
    }
}
