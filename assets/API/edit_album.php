<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 04/04/2017
 * Time: 16:56
 */

require_once '../../vendor/autoload.php';

use Lib\FileUtils;
use Lib\MusicClasses\Album;

function editAlbum($data)
{
    $id = null;

    if (!isset($data->id)) {
        throw new Exception("Album ID not provided.");
    } else {
        $id = $data->id;
    }

    $album = Album::getAlbum($id);

    if ($album == null) {
        throw new Exception("Album ID not found in database.");
    }

    if (isset($data->cover)) {
        $cover = FileUtils::normaliseUrl($data->cover);

        $album->setCover($cover);
    }

    if (isset($data->title)) {
        $album->setTitle($data->title);
    }

    $album->save();
}

try {
    $data = json_decode(file_get_contents('php://input'));

    editAlbum($data);

    echo "success";
} catch (Exception $e) {
    echo $e->getMessage();
}