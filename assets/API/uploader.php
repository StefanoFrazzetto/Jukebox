<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../php-lib/FileUtils.php';
require_once __DIR__ . '/../php-lib/DiscRipper.php';
require_once __DIR__ . '/../php-lib/Uploader.php';


$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
$uploader_id = filter_input(INPUT_GET, 'uploader_id', FILTER_SANITIZE_STRING);


$return = ['status' => 'success'];

switch ($action) {
    case 'get_ripper_status':
        $ripper = new DiscRipper();
        $return = [
            'device_path' => $ripper->getDevicePath(),
            'status' => $ripper->getStatus(),
            'percentage' => $ripper->getPercentage(),
            'message' => $ripper->getMessage(),
            'ripped' => $ripper->getRippedTracks(),
            'encoded' => $ripper->getEncodedTracks()
        ];
        break;

    case 'start_ripping':
        if (empty($uploader_id)) {
            $return['status'] = 'fail';
            $return['started'] = false;
        }
        $ripper = new DiscRipper($uploader_id);
        $return['started'] = $ripper->rip();
        if (!$return['started']) {
            $return['status'] = 'fail';
        }
        break;

    case 'get_new_id':
        $return['uploader_id'] = Uploader::getNewUploaderID();
        break;

    case 'abort_upload':
        $path = Config::getPath('uploader');
        $return['success'] = FileUtils::remove($path, true);
        break;

    case 'list_uploads_in_progress':
        $path = Config::getPath('uploader');
        $return['uploader_ids'] = FileUtils::getDirectories($path);
        break;

    default:
        $return['status'] = 'error';
}

echo json_encode($return);