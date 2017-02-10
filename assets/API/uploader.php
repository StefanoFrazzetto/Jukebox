<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../php-lib/FileUtils.php';
require_once __DIR__ . '/../php-lib/DiscRipper.php';
require_once __DIR__ . '/../php-lib/Uploader.php';


$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
$uploader_id = filter_input(INPUT_GET, 'uploader_id', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);

if ($uploader_id === null) {
    $uploader_id = Uploader::getNewUploaderID();
}

// Initialize the array assuming that everything was successful
$return = ['status' => Uploader::STATUS_SUCCESS];

switch ($action) {
    case 'upload_files':
        try {
            Uploader::upload($uploader_id);
        } catch (Exception $e) {
            $return = Uploader::createStatus(Uploader::STATUS_ERROR, $e->getMessage());
        }
        break;

    case 'get_ripper_status':
        $ripper = new DiscRipper();
        $return = [
            'status' => $ripper->getStatus(),
            'percentage' => $ripper->getPercentage(),
            'message' => $ripper->getMessage()
        ];
        break;

    case 'start_ripping':
        try {
            $ripper = new DiscRipper($uploader_id);
            $ripper->rip();
            $return['status'] = Uploader::STATUS_SUCCESS;
        } catch (Exception $e) {
            $return = Uploader::createStatus(Uploader::STATUS_ERROR, $e->getMessage());
        }
        break;

    case 'get_new_id':
        $return['uploader_id'] = Uploader::getNewUploaderID();
        break;

    case 'abort_upload':
        $path = Config::getPath('uploader');
        if (!FileUtils::remove($path, true)) {
            $return['status'] = Uploader::STATUS_ERROR;
        }
        break;

    case 'list_uploads_in_progress':
        $path = Config::getPath('uploader');
        $return['uploader_ids'] = FileUtils::getDirectories($path);
        break;

    default:
        $return['status'] = 'error';
}

echo json_encode($return);