<?php

namespace Lib;

use Exception;
use Symfony\Component\Finder\Finder;

/**
 * Class UpdaterManager handles the software updates using the update files
 * provided in the updater directory.
 */
class UpdaterManager
{
    /** @var */
    const UPDATER_FILE_NAME_PATTERN = '/^\d+_([\w_]+).json/i';

    /** @var */
    const UPDATER_TABLE_NAME = 'updaterlog';

    /** @var string the directory containing the migrations */
    private $updatesPath;

    /** @var bool $errors */
    private $errors = false;

    /** @var array $updates */
    private $updates;

    /** @var bool $debug_mode true if running automated tests */
    private $debug_mode = false;

    public function __construct($debug_mode = false)
    {
        $this->debug_mode = $debug_mode;

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
     * Run the updates.
     *
     * @throws Exception if the updaters is not ready
     */
    public function run()
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
                    $ret = !$this->debug_mode ? $this->addToDatabase($update) : true;
                } else {
                    $this->errors = true;
                    break;
                }
            }
        }

        return $ret;
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

    /**
     * Set up the class.
     *
     * @throws Exception
     */
    private function setup()
    {
        $updatesPath = Config::getPath('updater');
        if (is_null($updatesPath)) {
            throw new Exception('Updater: cannot find the updates directory');
        }

        $this->updatesPath = $updatesPath;

        $database = new Database();
        if (!$database->tableExists(static::UPDATER_TABLE_NAME)) {
            throw new Exception('The updater table does not exist');
        }
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
        $files = $finder->in($this->updatesPath)->files();

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
     * @param string $fileName file name
     *
     * @return bool
     */
    public static function isValidMigrationFileName($fileName)
    {
        $matches = [];

        return preg_match(static::UPDATER_FILE_NAME_PATTERN, basename($fileName), $matches);
    }
}
