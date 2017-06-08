<?php

require_once '../../vendor/autoload.php';

use Lib\Config;
use Lib\Cover;
use Lib\Database;
use Lib\FileUtils;

class EditAlbum
{
    private $database;

    // Constructor
    public function __construct($json)
    {
        $this->database = new Database();
        $response = ['success' => false, 'message' => ''];
        $array = json_decode($json, true);
        $album_id = $array['album_id'];

        // DELETE THE ALBUM
        if (isset($array['delete_album']) && $array['delete_album'] === true) {
            $response['success'] = $this->database->delete(Database::$_table_albums, " `id` = $album_id");

            if ($response['success'] === false) {
                $response['message'] = 'Failed to delete the album.';
            }
        } else {

            // UPDATE THE ALBUM
            if (isset($array['removed_tracks'])) {
                $tracks = json_decode($array['removed_tracks'], true);
                $this->removeTracks($album_id, $tracks);
            }

            if (isset($array['album_cover_url']) && $array['album_cover_url'] !== null) {
                $cover = new Cover($array['album_cover_url']);
                $cover->saveToAlbum($album_id);
            }

            $data = ['title' => $array['album_title']];

            // file_put_contents("/tmp/EDIT_ALBUM-SAVE.txt", json_encode($data));

            $response['success'] = $this->save($data, $album_id);

            if ($response['success'] === false) {
                $response['message'] = 'Error: could not save the album.';
            }
        }

        echo json_encode($response);
    }

    // The tracks column will be "overridden", so no need to delete them manually from the DB.
    protected function removeTracks($album_id, $tracks)
    {
        foreach ($tracks as $track) {
            $track_path = Config::getPath('albums_root').$album_id.'/'.$track['url'];
            FileUtils::remove($track_path);
        }
    }

    protected function save($array, $album_id)
    {
        return $this->database->update(Database::$_table_albums, $array, " `id` = $album_id");
    }
}

$json = file_get_contents('php://input');
// file_put_contents("/tmp/EDIT_ALBUM-POST.txt", $json);
$EditAlbum = new EditAlbum($json);
