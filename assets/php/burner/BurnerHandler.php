<?php

require_once 'autoload.php';

// if (!isset($_SESSION['burner_CD'])) {
// 	$_SESSION['burner_CD'] = 1;
// }

class BurnerHandler {

	public static $_burner_folder = "/tmp/burner/";
	public static $_burner_tracks_json = "/tmp/burner_tracks.json"; 
	public static $_burner_status_file = "/tmp/burner_status.json";
	public static $_burner_scripts = "./scripts";
	public static $_burner_abs_scripts = "/var/www/html/assets/php/burner/scripts";

	private static $_status_idle = "Idle";
	private static $_status_decoding = "Decoding";
	private static $_status_normalizing = "Normalizing";
	private static $_status_burning = "Burning";

	function __construct($action = "check", $input_type = "", $input_content = "", $output_format = ""){

		if($input_content == "" || empty($input_content) || !(isset($input_content)) || empty($output_format))
		{
			$action = "check";
		}

		// Override for next CD
		if($input_content == "nextCD") {
			$action = "burn";
		}

		$discWriter = new DiscWriter();

		switch ($action) {
			case 'info':
				if($discWriter->checkDiscBlank()) {
					$TracksHandler = new TracksHandler($input_type, $input_content, $output_format);
					$output['status'] = "Idle";
					$output['message'] = "CDs required: ".$TracksHandler->getRequiredCDS();

					if($output_format == "mp3") {
						$output['message'] .= " - Compilation size: " . $TracksHandler->getCompilationSize() . " MB";
					}
				} else {
					$output['status'] = "Error";
					$output['message'] = "Your disc is not blank!";
				}
			break;

			case 'burn':
				$Burner = new Burner($output_format);

				// If the burner is not doing anything, start the process and update
				$status = self::checkStatus();
				$output['status'] = $status;
				if($status == self::$_status_idle) {
					if(!($discWriter->checkDiscBlank())) {
						$output['status'] = "Error";
						$output['message'] = "Your disc is not blank!";
						break;
					}

					$tracks = TracksHandler::getTracksJSON();
					if(count($tracks) == 0) {
						unset($tracks);
						CommandExecuter::removeDir(self::$_burner_folder);
					} else {
                        //Start the burning process.
                        $Burner->burn();
                        $output['status'] = "Copying";
                        $output['message'] = "Please wait...The process has been started.";
                    }
				}	
			break;

			case 'check':
				$output['status'] = self::checkStatus();
			break;

			default:
				$output['status'] = "Error";
				$output['message'] = "No action specified. File: BurnerHandler.php";
		}

		echo json_encode($output);
	}

    /**
     * Check the burner status using the process.
     *
     * @return string
     */
	private static function checkStatus() {
		if(CommandExecuter::isProcessRunning("lame")){
			$status = self::$_status_decoding;

		} elseif (CommandExecuter::isProcessRunning("normalize-audio")) {
			$status = self::$_status_normalizing;

		} elseif (CommandExecuter::isProcessRunning("mkisofs") || CommandExecuter::isProcessRunning("genisoimage")) {
			$status = self::$_status_burning;

		} elseif (CommandExecuter::isProcessRunning("cdrecord")) {
			$status = self::$_status_burning;

		} else {
			$status = self::$_status_idle;
		}

		return $status;
	}
	
}

$action = isset($_POST['action']) ? $_POST['action'] : "";
$input_type = isset($_POST['input_type']) ? $_POST['input_type'] : "";
$input_content = isset($_POST['input_content']) ? $_POST['input_content'] : "";
$output_format = isset($_POST['output_format']) ? $_POST['output_format'] : "";

if($action != "") {
	$BurnerHandler = new BurnerHandler($action, $input_type, $input_content, $output_format);
}