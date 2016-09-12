<?php

require_once 'autoload.php';

/**
*	This is the class for the DISC WRITER.
*	Created by Stefano on 16/05/2016
*	Last update: 18/06/2016
*/
class DiscWriter {

	/** Device ID */
	private $device;

	/** Disc Size */
	private $disc_size;

	/** Create a new instance */
	public function __construct()
	{
		$this->device = CommandExecuter::getRomId();
		$this->disc_size = $this->setDiscSize();
	}

	/**
	*	Get the disc size in KB.
	*	@return int
	*/
	private function setDiscSize() {
		global $scripts;
		// PRINT VARIABLES
		$device_size = shell_exec("bash $scripts/get_cd_capacity.sh $this->device");
		$size = preg_replace('/[^0-9]+/', '', $device_size);

		return $size;
	}

//	public static function checkDiscBlank() {
//		$cmd = shell_exec("lsblk | grep rom | awk {'print $4'} | sed 's/[^0-9]*//g'");
//		$disc_check = shell_exec("cdrecord -v dev=/dev/sr0 -toc 2>&1");
//
//		if ($cmd < 10 && substr_count($disc_check, "Cannot load media") == 0) {
//			return TRUE;
//		} else {
//			return FALSE;
//		}
//	}

    /**
     * Check if the disc is blank.
     *
     * @return boolean
     * TRUE if the disc is blank, FALSE otherwise.
     */
    public function checkDiscBlank() {
        $command = CommandExecuter::raw("assets/cmd/CdCheck.sh");

        if (strpos($command, 'Unknown') !== false) {
            return false;
        } elseif (strpos($command, 'DVD') !== false) {
            $this->dvdCheckBlank();
        } elseif (strpos($command, 'CD-ROM') !== false) {
            return false;
        } elseif (strpos($command, 'CD') !== false) {
            return true;
        }

        return false;
    }

//    private function dvdCheckBlank() {
//        $dvdstatus = CommandExecuter::rawWithOutput("dvd+rw-mediainfo /dev/$this->device | grep -e \"Disc status:\" | awk {'print $3'}");
//
//        if ($dvdstatus == 'blank') {
//            return true;
//        } else {
//            return false;
//        }
//    }

	public function getDiscSize() {
		return $this->disc_size;
	}

	public function burnDisc($directory, $output_format) {
		global $scripts;

		putenv("input_directory=$directory");
		putenv("device=$this->device");
		putenv("output_format=$output_format");

		shell_exec("$scripts/burner-handler.sh > /dev/null 2>&1 &");
		
	}

}