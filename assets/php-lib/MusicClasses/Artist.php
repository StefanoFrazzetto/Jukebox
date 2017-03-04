<?php

namespace Lib\MusicClasses;

use Exception;
use JsonSerializable;
use Lib\Database;

/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 20/12/2016
 * Time: 19:55.
 */
class Artist implements JsonSerializable
{
    const ARTIST_TABLE = 'artists';

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    private $created = false;

    public function __construct($name)
    {
        $this->setName($name);
    }

    /**
     * Returns an Artist from database, null if not found.
     *
     * @param $id int
     *
     * @return Artist | null
     */
    public static function getArtist($id)
    {
        $db = new Database();

        $db_object = $db->select('*', self::ARTIST_TABLE, "WHERE `id` = $id");

        if (!isset($db_object[0])) {
            return;
        }

        return self::makeArtistFromDatabaseObject($db_object[0]);
    }

    /**
     * @param $db_object object
     *
     * @return Artist|null
     */
    private static function makeArtistFromDatabaseObject($db_object)
    {
        try {
            $artist = new self($db_object->name);

            $artist->created = true;

            $artist->id = intval($db_object->id);

            return $artist;
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * Gets all the artists participating in an album.
     *
     * @param $id int the album id
     *
     * @return array | bool
     */
    public static function getArtistIdsInAlbum($id)
    {
        $db = new Database();

        $db_object = $db->rawQuery(
            "SELECT DISTINCT song_artists.artist_id
            FROM song_artists
            INNER JOIN songs ON song_artists.song_id = songs.id
            WHERE songs.album_id = $id"
        );

        if (!is_array($db_object)) {
            $db_object = [];
        }

        return $db_object;
    }

    public static function getAllArtists()
    {
        $db = new Database();

        $db_object = $db->select('*', self::ARTIST_TABLE);

        $artists = [];

        if (is_array($db_object)) {
            foreach ($db_object as $artist) {
                $artists[] = self::makeArtistFromDatabaseObject($artist);
            }
        }

        return $artists;
    }

    /**
     * Looks for an artist in the database returns the first match if found, creates one otherwise.
     *
     * @param $name
     *
     * @return Artist
     */
    public static function softCreateArtist($name)
    {
        $search = self::findArtistByTitle($name);

        if (count($search)) {
            return $search[0];
        }

        $artist = new self($name);

        $artist->save();

        return $artist;
    }

    /**
     * Returns an array of artists that matches the given name.
     *
     * @param $name string name of the artist
     *
     * @return Artist[]
     */
    public static function findArtistByTitle($name)
    {
        $db = new Database();

        $db_objects = $db->select('*', self::ARTIST_TABLE, "WHERE `name` LIKE '$name'");

        $artists = [];

        if (is_array($db_objects)) {
            foreach ($db_objects as $db_object) {
                $artists[] = self::makeArtistFromDatabaseObject($db_object);
            }
        }

        return $artists;
    }

    /**
     * Saves or update the Artist to the database.
     *
     * @return bool
     */
    public function save()
    {
        $database = new Database();

        if ($this->created) {
            return $database->update(self::ARTIST_TABLE, ['name' => $this->name], "`id` = $this->id");
        } else {
            $status = $database->insert(self::ARTIST_TABLE, ['name' => $this->name]);

            if (!$status) {
                return false;
            }

            $this->id = intval($database->getLastInsertedID());
            $this->created = true;
        }

        return true;
    }

    /**
     * Deletes an artist from database.
     *
     * @return bool
     */
    public function delete()
    {
        $this->created = false;

        return self::deleteArtist($this->id);
    }

    /**
     * Deletes an artist form database.
     *
     * @param $id int
     *
     * @return bool status of the db
     */
    public static function deleteArtist($id)
    {
        $db = new Database();

        $db->delete(Song::SONG_ARTISTS_TABLE, "`artist_id` = $id");

        return $db->delete(self::ARTIST_TABLE, "`id` = $id");
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = stripslashes($name);
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource.
     *
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return ['id' => $this->id, 'name' => $this->name];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
