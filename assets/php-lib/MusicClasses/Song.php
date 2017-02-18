<?php

namespace Lib\MusicClasses;

use Exception;
use JsonSerializable;
use Lib\Database;

/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 20/12/2016
 * Time: 19:55
 */
class Song implements JsonSerializable
{
    const SONGS_TABLE = 'songs';
    const SONG_ARTISTS_TABLE = 'song_artists';

    /**
     * @var int
     */
    private $id;
    /**
     * @var int
     */
    private $album_id;
    /**
     * @var int
     */
    private $cd;
    /**
     * @var int
     */
    private $track_no;
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $file_path;

    /**
     * @var string
     */
    private $url;

    /**
     * @var int length of the track in seconds
     */
    private $length;
    /**
     * @var bool
     */
    private $created = false;

    /**
     * @var Artist[] the artists of the song
     */
    private $artists = [];

    /**
     * Factory method that return a song object from a file
     * @param $file string
     * @return Song
     * @throws Exception in case the $file is missing
     */
    public static function loadSongFromFile($file)
    {
        if (!file_exists($file))
            throw new Exception("The song '$file' could not be found");

        $song = new Song();

        $song->setUrl(basename($file));

        $song->file_path = dirname($file);

        $song->length = Song::getLength($file);

        return $song;
    }

    /**
     * @param $file string the file to get the length from
     * @return int length of seconds of the mp3 file
     */
    public static function getLength($file)
    {
        $length = shell_exec('mp3info -p "%S" ' . $file);

        intval($length);

        return $length;

    }

    /**
     * Creates a song object out of a legacy song json
     * @param $json object json
     * @param $album_id int
     * @return Song
     */
    public static function importSongFromJson($json, $album_id)
    {
        $song = new Song();

        $song->setTitle($json->title);

        if (isset($json->track_no)) {
            $song->setTrackNo($json->track_no);
        }

        $song->setCd($json->cd);

        $song->setUrl($json->url);

        $song->setLength($json->length);

        $song->setAlbumId($album_id);

        return $song;
    }

    /**
     * @param int $length
     */
    public function setLength($length)
    {
        $this->length = intval($length);
    }


    // HERE TO BE DRAGONS

    /**
     * Returns a Song from database, null if not found
     * @param $id int
     * @return Song | null
     */
    public static function getSong($id)
    {
        $db = new Database();

        $db_object = $db->select('*', Song::SONGS_TABLE, "WHERE `id` = $id");

        if (!isset($db_object[0]))
            return null;

        return self::makeSongFromDatabaseObject($db_object[0]);
    }

    /**
     * @param $db_object object
     * @return Song|null
     */
    private static function makeSongFromDatabaseObject($db_object)
    {
        try {
            $song = new Song("jukebox/$db_object->album_id/$db_object->url");

            $song->created = true;

            $song->id = intval($db_object->id);

            $song->album_id = intval($db_object->album_id);

            $song->cd = intval($db_object->cd);

            $song->track_no = intval($db_object->track_no);

            $song->title = $db_object->title;

            $song->length = intval($db_object->length);

            $song->url = $db_object->url;

            return $song;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Returns all the songs in an album, given the album's id
     * @param $id int album Id
     * @return Song[]|null
     */
    public static function getSongsInAlbum($id)
    {
        $db = new Database();

        $db_objects = $db->select('*', Song::SONGS_TABLE, "WHERE `album_id` = $id");

        if (!is_array($db_objects))
            $db_objects = [];

        $songs = [];

        foreach ($db_objects as $db_object) {
            $songs[] = self::makeSongFromDatabaseObject($db_object);
        }

        return $songs;
    }

    /**
     * Saves a batch of Songs to the database
     * @param $songs Song[]
     */
    public static function saveMultiple($songs)
    {
        // FIXME this could be made more efficient using a single query, maybe?
        foreach ($songs as $song) {
            if (!$song instanceof Song)
                throw new \InvalidArgumentException("You must pass only Song type");
            if ($song->save() === false) {
                throw new \RuntimeException("Failed to save one of the song");
            }
        }
    }

    /**
     * Saves or update the song to the database
     * @return bool
     */
    public function save()
    {
        $database = new Database();

        $arr = [ // I AM A PIRATE!
            'album_id' => $this->album_id, 'cd' => $this->cd, 'track_no' => $this->track_no,
            'title' => $this->title, 'url' => $this->url, 'length' => $this->length
        ];

        if ($this->created) {
            echo $this->id;
            return $database->update(self::SONGS_TABLE, $arr, "`id` = $this->id");
        } else {
            $status = $database->insert(self::SONGS_TABLE, $arr);

            if (!$status) {
                return false;
            }

            $this->id = intval($database->getLastInsertedID());
            $this->created = true;
        }

        return true;
    }

    /**
     * @return array|null of ID3v2 Tags
     */
    public function getID3Tags()
    {
        $cmd = 'id3v2 -R "' . $this->getFullUrl() . '"';

        $output = shell_exec($cmd);

        $count = preg_match_all("/^([a-zA-Z]*[0-3]?):\\s*(.*)$/m", $output, $matches);

        if ($count < 1)
            return null;

        $results = [];

        foreach ($matches[1] as $key => $match) {
            if (!in_array($match, ['PRIV', 'COMM']))
                $results[$match] = $matches[2][$key];
        }

        return $results;
    }

    public function getFullUrl()
    {
        if ($this->created)
            return "/jukebox/$this->album_id/$this->url";
        else
            return $this->file_path . '/' . $this->url;
    }

    /**
     * Deletes a song from database
     * @return bool
     */
    public function delete()
    {
        $this->created = false;

        $file = __DIR__ . $this->getFullUrl();

        if (file_exists($file))
            unlink($file);
        else if (file_exists($this->getUrl()))
            unlink($this->getUrl());

        return self::deleteSong($this->id);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Deletes a song form database
     * @param $id int
     * @return bool status of the db
     */
    public static function deleteSong($id)
    {
        $db = new Database();

        return $db->delete(self::SONGS_TABLE, "`id` = $id");
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [
            'id' => $this->id, 'album_id' => $this->album_id, 'cd' => $this->cd, 'track_no' => $this->track_no,
            'title' => $this->title, 'artists' => $this->getArtistsIds(), 'url' => $this->url, 'length' => $this->length
        ];
    }

    /**
     * Return an arrays with the artists IDs of the song.
     * @return array
     */
    public function getArtistsIds()
    {
        $db = new Database();
        $results = $db->select('artist_id', self::SONG_ARTISTS_TABLE, "WHERE song_id = $this->id");

        if (!is_array($results))
            return [];

        $return = [];

        foreach ($results as $result) {
            $return[] = intval($result->artist_id);
        }

        return $return;
    }

    /**
     * @return Artist[]
     */
    public function getArtists()
    {
        $ids = $this->getArtistsIds();

        $artists = [];
        foreach ($ids as $id) {
            $artists[] = Artist::getArtist($id);
        }

        return $artists;
    }

    /**
     * @return int
     */
    public function getAlbumId()
    {
        return $this->album_id;
    }

    /**
     * @param int $album_id
     */
    public function setAlbumId($album_id)
    {
        $this->album_id = intval($album_id);
    }

    /**
     * @return int
     */
    public function getCd()
    {
        return $this->cd;
    }

    /**
     * @param int $cd
     */
    public function setCd($cd)
    {
        $this->cd = intval($cd);
    }

    /**
     * @return int
     */
    public function getTrackNo()
    {
        return $this->track_no;
    }

    /**
     * @param int $track_no
     */
    public function setTrackNo($track_no)
    {
        $this->track_no = intval($track_no);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Adds an artist to the song
     * @param $id int the artist id
     */
    public function addArtist($id)
    {
        $db = new Database();

        $db->insert(self::SONG_ARTISTS_TABLE, ['song_id' => $this->getId(), 'artist_id' => $id]);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string a player friendly time expressed in mm:ss
     */
    public function getTimeString()
    {
        $addZeros = function ($digit) {
            if ($digit < 10) {
                $digit = '0' . $digit;
            }
            return $digit;
        };

        $minutes = floor($this->length / 60);
        $seconds = $this->length - $minutes * 60;

        return $addZeros($minutes) . ':' . $addZeros($seconds);
    }
}