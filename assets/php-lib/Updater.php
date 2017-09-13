<?php
/**
 * Created by PhpStorm.
 * User: stefano
 * Date: 12/09/17
 * Time: 12:52.
 */

namespace Lib;

use Exception;
use InvalidArgumentException;
use Symfony\Component\Finder\Finder;

class Updater
{
    /** @var string $updates_dir the directory containing the updates */
    private $updates_dir;

    /** The log level for the updater. Max log level is 2 */
    const LOG_LEVEL = 2;

    public function __construct()
    {
        $config = new Config();
        $this->updates_dir = $config->get('paths')['updates'];
    }

    /**
     * Run the update files.
     *
     * @return array
     */
    public function run()
    {
        $finder = new Finder();
        $finder->ignoreUnreadableDirs()->in($this->updates_dir)->files()->sortByModifiedTime();

        $update_no = 1;
        $res = [];
        foreach ($finder as $file) {
            $res[$update_no]['filename'] = basename($file);
            $res[$update_no]['results'] = $this->readUpdateFile($file);
            $update_no++;
        }

        return $res;
    }

    /**
     * Launch the update file.
     *
     * @param $file
     *
     * @throws Exception
     *
     * @return array
     */
    private function readUpdateFile($file)
    {
        $encoded_file_content = file_get_contents($file);
        if (!$encoded_file_content) {
            throw new Exception('Cannot read the update file');
        }
        $file_content = json_decode($encoded_file_content, true);

        $aptitude_commands = $file_content['aptitude'];
        $raw_commands = $file_content['raw'];

        // The results
        $res = [];
        $res['aptitude'] = $this->aptitude($aptitude_commands);
        $res['raw'] = $this->raw($raw_commands);

        return $res;
    }

    /**
     * Install, remove, or update the software packages contained in the aptitude
     * section of the update file.
     *
     * @param array $aptitude_commands
     *
     * @return array
     */
    private function aptitude($aptitude_commands)
    {
        if (empty($aptitude_commands)) {
            throw new InvalidArgumentException('Action and arguments must not be empty');
        }

        $res = [];
        foreach ($aptitude_commands as $action => $args) {
            $arguments = implode(' ', $args);
            $cmd = "sudo apt-get $action $arguments -y";
            $res[] = $this->execute($cmd);
        }

        return $res;
    }

    /**
     * Execute the raw commands contained in the update file.
     *
     * @param array $commands the raw commands to execute
     *
     * @return array @see execute
     */
    private function raw($commands)
    {
        $res = [];
        foreach ($commands as $command) {
            $cmd = !empty($commands) ? $command : 'Nothing to execute';
            $res[] = $this->execute($cmd);
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
    private function execute($cmd)
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

        return $res;
    }
}
