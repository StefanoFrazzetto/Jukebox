<?php

namespace Lib;

use Exception;
use InvalidArgumentException;

class UpdaterFile
{
    /** The log level for the updater. Max log level is 2 */
    const LOG_LEVEL = 2;

    /** @var string $version */
    private $version;

    /** @var string $file_path */
    private $file_path;

    /** @var string $file_name */
    private $file_name;

    /** @var string $start_time */
    private $start_time;

    /** @var string $end_time */
    private $end_time;

    /** @var array $update_content the content of the update file */
    private $update_content;

    /** @var bool $is_valid flag whether the file is valid or not */
    private $is_valid;

    /** @var bool $STATUS_SUCCESS */
    private $STATUS_SUCCESS = false;

    public function __construct($updateFile)
    {
        if (empty($updateFile)) {
            throw new InvalidArgumentException('The update file cannot be empty');
        }

        $this->file_path = $updateFile;
        $this->file_name = basename($this->file_path);
        $this->version = static::getVersionFromName($this->file_name);

        try {
            $this->readUpdateFile();
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->is_valid = false;
        }
    }

    public function getFileName()
    {
        return $this->file_name;
    }

    public function isValid()
    {
        return $this->is_valid;
    }

    public function wasSuccessful()
    {
        return $this->STATUS_SUCCESS;
    }

    public function getStartTime()
    {
        return $this->start_time;
    }

    public function getEndTime()
    {
        return $this->end_time;
    }

    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Read the update file.
     *
     * @throws Exception
     */
    private function readUpdateFile()
    {
        // TODO: add validation here.

        $file_content = file_get_contents($this->file_path);
        if ($file_content === false) {
            throw new Exception('Cannot read the update file');
        }

        $json = json_decode($file_content, true);
        if ($json === null) {
            throw new Exception('The file file_content is not valid');
        }

        $this->update_content = $json;
        $this->is_valid = true;
    }

    /**
     * Execute the update file.
     *
     * @throws Exception
     *
     * @return array
     */
    public function execute()
    {
        if (!$this->is_valid) {
            throw new Exception('The update file is not valid. Cannot run the update');
        }

        // Set the start time
        $this->start_time = StringUtils::getCurrentDatetime();

        $res = [];
        if (isset($this->update_content['aptitude'])) {
            $res['aptitude'] = $this->aptitude();
        }

        if (isset($this->update_content['raw'])) {
            $res['raw'] = $this->raw();
        }

        // Set the end time
        $this->end_time = static::getCurrentTime();

        return $res;
    }

    /**
     * Install, remove, or update the software packages contained in the aptitude
     * section of the update file.
     *
     * @return array
     */
    private function aptitude()
    {
        $aptitude_commands = $this->update_content['aptitude'];

        $res = [];
        foreach ($aptitude_commands as $action => $args) {
            if (!empty($action) && !empty($args)) {
                $arguments = implode(' ', $args);
                $cmd = "sudo apt-get $action $arguments -y";
                $res[] = $this->run($cmd);
            }
        }

        return $res;
    }

    /**
     * Execute the raw commands contained in the update file.
     *
     * @return array
     */
    private function raw()
    {
        $commands = $this->update_content['raw'];

        $res = [];
        foreach ($commands as $command) {
            if (!empty($command)) {
                $res[] = $this->run($command);
            }
        }

        return $res;
    }

    /**
     * Execute the command and create the output response.
     *
     * @param string $cmd the command to execute
     *
     * @return array the array containing status, command, and message from the command
     */
    private function run($cmd)
    {
        $res = ['success' => true];

        if (!empty($cmd)) {
            exec($cmd.' 2>&1', $output, $return_code);
        } else {
            $return_code = 0;
            $output = 'Nothing to execute.';
        }

        switch (self::LOG_LEVEL) {
            case 2:
                $res = ['success' => $return_code === 0, 'command' => $cmd, 'message' => $output];
                break;

            case 1:
                $res = ['success' => $return_code === 0, 'message' => $output];
                break;

            case 0:
            default:
                if ($return_code !== 0) {
                    $res = ['success' => false, 'message' => $output];
                }
        }

        $this->STATUS_SUCCESS = $return_code === 0;

        return $res;
    }

    /**
     * Get the version from the beginning of a file name.
     *
     * @param string $fileName file name
     *
     * @return string
     */
    public static function getVersionFromName($fileName)
    {
        $matches = [];
        preg_match('/^[0-9]+/', $fileName, $matches);

        return $matches[0];
    }
}
