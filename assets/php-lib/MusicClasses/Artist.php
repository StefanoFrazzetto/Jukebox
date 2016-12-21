<?php

include_once __DIR__ . '/../../php/Database.php';

/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 20/12/2016
 * Time: 19:55
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

    function __construct($name)
    {
        $this->setName($name);
    }

    /**
     * Returns an Artist from database, null if not found
     * @param $id int
     * @return Artist | null
     */
    public static function getArtist($id)
    {
        $db = new Database();

        $db_object = $db->select('*', Artist::ARTIST_TABLE, "WHERE `id` = $id");

        if (!isset($db_object[0]))
            return null;

        return self::makeArtistFromDatabaseObject($db_object[0]);
    }

    /**
     * @param $db_object object
     * @return Artist|null
     */
    private static function makeArtistFromDatabaseObject($db_object)
    {
        try {
            $artist = new Artist($db_object->name);

            $artist->created = true;

            $artist->id = $db_object->id;

            return $artist;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Deletes an artist from database
     * @return bool
     */
    public function delete()
    {
        $this->created = false;
        return self::deleteArtist($this->id);
    }

    /**
     * Deletes an artist form database
     * @param $id int
     * @return bool status of the db
     */
    public static function deleteArtist($id)
    {
        $db = new Database();

        return $db->delete(Artist::ARTIST_TABLE, "`id` = $id");
    }

    /**
     * Saves or update the Artist to the database
     * @return bool
     */
    public function save()
    {
        $database = new Database();

        if ($this->created) {
            return $database->update(Artist::ARTIST_TABLE, ["name" => $this->name], "`id` = $this->id");
        } else {
            $status = $database->insert(Artist::ARTIST_TABLE, ["name" => $this->name]);

            if (!$status) {
                return false;
            }

            $this->id = $database->getLastInsertedID();
            $this->created = true;
        }

        return true;
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
        $this->name = $name;
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
        return ["id" => $this->id, "name" => $this->name];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}