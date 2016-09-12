<?php

abstract class CommandExecuter {

	public static function getDevicePath($device = '') {
		if($device == '') {
			return null;
		}

		$device = shell_exec("lsblk | grep $device | cut -d' ' -f1");
		return $device;
	}

	public static function getRomId()
	{
		$rom = shell_exec("less /proc/sys/dev/cdrom/info | grep 'drive name:'| awk {'print $3'}");
		// Remove the fucking carriage return!
		$rom = str_replace(array('.', ' ', "\n", "\t", "\r"), '', $rom);
		return $rom;
	}

	public static function isProcessRunning($process) {
		if (shell_exec("pidof -x $process") != "") {
        	return true;
	    } else {
	        return false;
		}
	}

	public static function removeDir($dir)
	{
		$cmd = shell_exec("rm -rfv $dir");
		return $cmd;
	}

	public static function raw($command) {
		$command = $command . " 2>&1";
		return shell_exec($command);
	}

    public static function rawWithOutput($command) {
        return shell_exec($command);
    }

}