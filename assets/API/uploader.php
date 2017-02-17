<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../../vendor/autoload.php';

use Lib\Config;
use Lib\DiscRipper;
use Lib\FileUtils;
use Lib\Uploader;
use Symfony\Component\Finder\Finder;

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
$uploader_id = filter_input(INPUT_GET, 'uploader_id', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);
$media_source = filter_input(INPUT_GET, 'media_source', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);
$json = filter_input(INPUT_GET, 'json', null, FILTER_NULL_ON_FAILURE);

// If the uploader id is not set and the action is different from getting a new id,
// return an error.
if (empty($uploader_id) && $action !== 'get_new_id') {
//    $return = Uploader::createStatus(Uploader::STATUS_ERROR, 'The uploader id is not set.', 400);
//    echo json_encode($return);
//    return;
}

// Initialize the array assuming that everything was successful
$return = ['status' => Uploader::STATUS_SUCCESS];

switch ($action) {
    case 'get_new_id':
        $return['uploader_id'] = Uploader::getNewUploaderID();
        break;

    case 'upload_files':
        try {
            Uploader::upload($uploader_id);
        } catch (Exception $e) {
            $return = Uploader::createStatus(Uploader::STATUS_ERROR, $e->getMessage(), 400);
        }
        break;

    case 'get_ripper_status':
        $ripper = new DiscRipper();

        $return = Uploader::createStatus($ripper->getStatus(), $ripper->getMessage());
        $return['percentage'] = $ripper->getPercentage();
        break;

    case 'start_ripping':
        try {
            $ripper = new DiscRipper($uploader_id);
            if (!$ripper->rip()) {
                $return = Uploader::createStatus(Uploader::STATUS_ERROR, 'the disc drive is busy');
            }
        } catch (Exception $e) {
            $return = Uploader::createStatus(Uploader::STATUS_ERROR, $e->getMessage());
        }
        break;

    case 'abort_ripping':
        $ripper = new DiscRipper();
        if (!$ripper->stop()) {
            $return = Uploader::createStatus(Uploader::STATUS_ERROR, 'it was not possible to stop the ripping process');
        }
        break;

    case 'abort_upload':
        $path = Config::getPath('uploader');
        if (!FileUtils::remove($path, true)) {
            $return['status'] = Uploader::STATUS_ERROR;
        }
        break;

    case 'list_uploads_in_progress':
        $path = Config::getPath('uploader');
        $finder = new Finder();
        $directories = $finder->in($path)->directories();

        $return['uploader_ids'] = [];
        foreach ($directories as $directory) {
            $return['uploader_ids'][] = $directory;
        }
        break;

    case 'get_tracks_json':
        try {
            $uploader = new Uploader($media_source);
            $return = $uploader->getAlbumInfo($uploader_id);
        } catch (Exception $e) {
            $return = Uploader::createStatus(Uploader::STATUS_ERROR, $e->getMessage(), 400);
        }
        break;

    case 'create_album':
        try {
            $uploader = new Uploader();
            $success = $uploader->createAlbumFromJson($json);

            if (!$success) {
                Uploader::createStatus(Uploader::STATUS_ERROR, 'Error while creating the album.');
            }
        } catch (Exception $e) {
            $return = Uploader::createStatus(Uploader::STATUS_ERROR, $e->getMessage(), 400);
        }
        break;

    default:
        $return['status'] = 'error';
}

echo json_encode($return);