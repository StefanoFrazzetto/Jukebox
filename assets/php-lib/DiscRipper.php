<?php

require_once "Disc.php";

class DiscRipper extends Disc
{
    /**
     * Initializes the attributes needed by the DiscWriter.
     */
    protected function __init()
    {
        $this->disc_id = OS::execute("discid $this->device_path");
    }

    public function getDiscID()
    {
        return $this->disc_id;
    }

    /**
     * Returns the number of tracks on the CD/DVD.
     *
     * @return int - the number of tracks on the CD/DVD
     */
    public function getTracksCount()
    {
        if ($this->total_tracks === null) {
            $this->total_tracks = OS::execute("cdparanoia -sQ -d $this->device_path |& grep -P -c '^\\s+\\d+\\.' | grep -E '[0-9]'");
        }

        return $this->total_tracks;
    }

    public function ripDisc($output_dir, $logs_path)
    {
        $log_file = $logs_path . "cdparanoia.log";
        $script = $this->scripts_path . "rip.sh";
        exec("$script $this->device_path $output_dir >> $log_file 2>&1 &");
    }
}