<?php

require_once __DIR__ . '/../../php/Database.php';

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


    // ============================

//    function __construct($url)
//    {
//        if (!file_exists($url))
//            throw  new Exception("The song '$url' could not be found");
//
//        $this->setUrl($url);
//    }

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

        $song->loadInfoFromMP3File();

        $song->setUrl(basename($file));

        $song->file_path = dirname($file);

        $song->length = Song::getLength($file);

        return $song;
    }

    public function loadInfoFromMP3File()
    {
        // TODO loadInfoFromMP3File
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

        $song->setTrackNo($json->track_no);

        $song->setCd($json->cd);

        $song->setUrl($json->url);

        $song->length = $json->length;

        $song->setAlbumId($album_id);

        return $song;
    }

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


    // HERE TO BE DRAGONS

    /**
     * @param $db_object object
     * @return Song|null
     */
    private static function makeSongFromDatabaseObject($db_object)
    {
        try {
            $song = new Song("jukebox/$db_object->album_id/$db_object->url");

            $song->created = true;

            $song->id = $db_object->id;

            $song->album_id = $db_object->album_id;

            $song->cd = $db_object->cd;

            $song->track_no = $db_object->track_no;

            $song->title = $db_object->title;

            $song->length = $db_object->length;

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

        $songs = [];

        foreach ($db_objects as $db_object) {
            $songs[] = self::makeSongFromDatabaseObject($db_object);
        }

        return $songs;
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

            $this->id = $database->getLastInsertedID();
            $this->created = true;
        }

        return true;
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
            'title' => $this->title, 'url' => $this->url, 'length' => $this->length
        ];
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
        $this->album_id = $album_id;
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
        $this->cd = $cd;
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
        $this->track_no = $track_no;
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
     * @return Artist[]
     */
    public function getArtists()
    {
        return $this->artists;
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