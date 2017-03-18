<?php

namespace Lib;

use Exception;

class DiscBurner extends Disc
{
    const OUTPUT_MP3 = 0;
    const OUTPUT_WAW = 1;

    /**
     * Initializes the attributes needed by the DiscWriter.
     */
    protected function __init()
    {
        // TODO: Implement init() method.
    }

    /**
     * Burn the tracks compilation or an album to the disc.
     *
     * @param string $tracks_directory the directory containing the
     * tracks to burn.
     * @param int $output_format the output format for the CD/DVD.
     *
     * @return true if the process is started, false in case of
     * errors.
     *
     * @throws Exception if there are no tracks to burn in the directory
     * or if the output format does not match the allowed ones.
     */
    public function burn($tracks_directory, $output_format)
    {
        // Check the current status
        if ($this->getStatus() !== static::STATUS_IDLE || !$this->checkDiskBlank()) {
            return false;
        }

        // Check the directory
        if (FileUtils::isDirEmpty($tracks_directory)) {
            throw new Exception('The directory must contain at least one track.');
        }

        // Check the output format
        if ($output_format !== static::OUTPUT_MP3 && $output_format !== static::OUTPUT_WAW) {
            throw new Exception('Invalid output format.');
        }

        $this->burnDisc($tracks_directory, $output_format);
        return true;
    }

    private function checkDiskBlank()
    {
        // TODO: Implement better check system.
        return true;
    }

    /**
     * @param string $directory the directory containing the tracks.
     * @param string $output_format the tracks output format.
     */
    private function burnDisc($directory, $output_format)
    {
        $conf = new Config();
        $output_log_dir = $conf->get('disc')['burner']['logs'];

        $script = $this->scripts_dir . 'burner-handler.sh';
        $arguments = [
            'input_directory' => $directory,
            'device' => $this->device_path,
            'output_format' => $output_format,
            'output_log_dir' => $output_log_dir
        ];

        OS::executeWithEnv($script, $arguments, true);
    }

    protected function updateStatus()
    {
        // TODO: Implement updateStatus() method.
    }
}
