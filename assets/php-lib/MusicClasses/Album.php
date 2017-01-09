<?php

require_once __DIR__ . '/../../php/Database.php';
require_once __DIR__ . '/Song.php';
require_once __DIR__ . '/../../php-lib/MusicClasses/Artist.php';
require_once __DIR__ . '/../../php/FileUtil.php';

/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 20/12/2016
 * Time: 12:10
 */
class Album implements JsonSerializable
{
    /**
     * string
     */
    const ALBUMS_TABLE = 'albums';

    const LAST_PLAYED_FILE = __DIR__ . '/../../config/lastid';

    /**
     * @var int database id
     */
    private $id;
    /**
     * @var string title of the album
     */
    private $title;
    /**
     * @var array the date the album was created
     */
    private $added_on;
    /**
     * @var int the number of times the album has been played
     */
    private $hits;

    /**
     * @var int an integer sorting the albums by the last time they were played
     */
    private $last_played;

    /**
     * @var int an integer representing an id from the genres table
     */
    private $genre;

    /**
     * @var bool if the album is stored in the database
     */
    private $stored;

    /**
     * @return Album[] all the Albums in the database
     */
    public static function getAllAlbums()
    {
        $db = new Database();

        $db_object = $db->select('*', self::ALBUMS_TABLE);

        $albums = [];

        foreach ($db_object as $album) {
            $albums[] = self::makeAlbumFromDatabaseObject($album);
        }

        return $albums;
    }

    /**
     * @param $db_object object
     * @return Album | null
     */
    private static function makeAlbumFromDatabaseObject($db_object)
    {
        try {
            $album = new Album();

            $album->setId($db_object->id);
            $album->setTitle($db_object->title);
            $album->setHits($db_object->hits);
            $album->setAddedOn($db_object->added_on);
            $album->setLastPlayed($db_object->last_played);
            $album->setGenre($db_object->genre);

            return $album;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param int $id
     */
    private function setId($id)
    {
        $this->id = intval($id);
    }

    /**
     * @param int $hits
     */
    private function setHits($hits)
    {
        $this->hits = intval($hits);
    }

    /**
     * @param array $added_on
     */
    private function setAddedOn($added_on)
    {
        $this->added_on = $added_on;
    }

    /**
     * @param int $last_played
     */
    private function setLastPlayed($last_played)
    {
        $this->last_played = intval($last_played);
    }

    /**
     * @param $id integer database id of the album
     * @return Album|null
     */
    public static function getAlbum($id)
    {
        $db = new Database();

        $db_object = $db->select('*', self::ALBUMS_TABLE, "WHERE `id` = $id");

        if (!isset($db_object[0]))
            return null;

        return self::makeAlbumFromDatabaseObject($db_object[0]);
    }

    /**
     * Saves or update the song to the database
     * @return bool
     */
    public function save()
    {
        $db = new Database();

        $arr = [ // I AM A PIRATE!
            'title' => $this->title,
            'genre' => $this->genre
        ];

        if ($this->stored) {
            echo $this->id;
            return $db->update(self::ALBUMS_TABLE, $arr, "`id` = $this->id");
        } else {
            $status = $db->insert(self::ALBUMS_TABLE, $arr);

            if (!$status) {
                return false;
            }

            $this->id = $db->getLastInsertedID();
            $this->stored = true;
        }

        return true;
    }

    /**
     * Updates statistics about the last album played and so on
     */
    public function play()
    {
        $this->hits++;

        $db = new Database();

        $db->increment(self::ALBUMS_TABLE, ['hits', 'last_played'], "id = $this->id");


        if ($this->getLastPlayed() != self::getLastPlayedId()) {
            $last = self::incrementLastPlayedId();

            $this->setLastPlayed($last);

            $db->update(self::ALBUMS_TABLE, ['last_played' => $last], "id = $this->id");
        }
    }

    /**
     * @return int
     */
    public function getLastPlayed()
    {
        return $this->last_played;
    }

    //<editor-fold desc="Getters and Setters" defaultstate="collapsed">

    /**
     * @return int the last played count
     */
    public static function getLastPlayedId()
    {
        if (file_exists(self::LAST_PLAYED_FILE))
            return intval(file_get_contents(self::LAST_PLAYED_FILE));

        return 0;
    }

    /**
     * Increments the count of the last played album
     * @return int the updated value
     */
    public static function incrementLastPlayedId()
    {
        $last = self::getLastPlayedId() + 1;

        file_put_contents(self::LAST_PLAYED_FILE, $last);

        return $last;
    }

    /**
     * Returns the number of tracks in the album
     * @return int the count of tracks
     */
    public function getTracksCount()
    {
        $db = new Database();

        return $db->count(Song::SONGS_TABLE, "album_id = $this->id");
    }

    /**
     * @return Song[] | null the songs in an album
     */
    public function getTracks()
    {
        return Song::getSongsInAlbum($this->getId());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getAddedOn()
    {
        return $this->added_on;
    }

    /**
     * @return int
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * @param int $genre
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;
    }

    /**
     *  Deletes the album and all the related files irreversibly
     */
    public function delete()
    {
        self::deleteAlbum($this->id);
    }

    /**
     * Deletes an album and all the related files irreversibly
     *
     * @param $id
     * @return bool
     */
    private static function deleteAlbum($id)

    {
        $database = new Database();

        FileUtil::removeDirectory(FileUtil::$_albums_root . "/$id");

        return $database->delete(self::ALBUMS_TABLE, "`id` = $id");
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
        return $this->serializableArray();
    }

    public function serializableArray()
    {
        return ["id" => $this->getId(), "title" => $this->getTitle(), "artists" => $this->getArtists(), "hits" => $this->getHits(), "last_played" => $this->getLastPlayed()];
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

    public function getArtists()
    {
        $raws = Artist::getArtistIdsInAlbum($this->getId());

        $arr = []; // I'm a pirate!

        foreach ($raws as $raw) {
            $arr[] = intval($raw[0]);
        }

        return $arr;
    }

    /**
     * @return int
     */
    public function getHits()
    {
        return $this->hits;
    }

    //</editor-fold>
}