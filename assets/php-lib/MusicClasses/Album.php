<?php

namespace Lib\MusicClasses;

use Exception;
use InvalidArgumentException;
use JsonSerializable;
use Lib\Config;
use Lib\Cover;
use Lib\Database;
use Lib\FileUtils;

/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 20/12/2016
 * Time: 12:10.
 */
class Album implements JsonSerializable
{
    /**
     * string.
     */
    const ALBUMS_TABLE = 'albums';

    //    const LAST_PLAYED_FILE = __DIR__ . '/../../config/lastid';
    const LAST_PLAYED_FILE = '/var/www/html/assets/config/lastid';

    const DATA_FILE = 'jukebox.json';

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

    /** @var string The albums root directory */
    private $albums_root;

    /**
     * Album constructor gets the albums root from Config.
     */
    public function __construct()
    {
        $this->albums_root = self::getAlbumsRoot();
    }

    /**
     * @return string the path to the albums root
     */
    public static function getAlbumsRoot()
    {
        return Config::getPath('albums_root');
    }

    /**
     * @return Album[] all the Albums in the database
     */
    public static function getAllAlbums()
    {
        $db = new Database();

        $db_object = $db->select('*', self::ALBUMS_TABLE);

        $albums = [];

        if (is_array($db_object)) {
            foreach ($db_object as $album) {
                $albums[] = self::makeAlbumFromDatabaseObject($album);
            }
        }

        return $albums;
    }

    /**
     * @return int[] all the ids of the albums in the database
     */
    public static function getAllAlbumsId()
    {
        $db = new Database();

        $db_object = $db->select('id', self::ALBUMS_TABLE);

        $ids = [];

        foreach ($db_object as $album) {
            $ids[] = intval($album->id);
        }

        return $ids;
    }

    /**
     * @param $db_object object
     *
     * @return Album | null
     */
    private static function makeAlbumFromDatabaseObject($db_object)
    {
        try {
            $album = new self();

            $album->setId($db_object->id);
            $album->setTitle($db_object->title);
            $album->setHits($db_object->hits);
            $album->setAddedOn($db_object->added_on);
            $album->setLastPlayed($db_object->last_played);
            if (isset($db_object->genre)) {
                $album->setGenre($db_object->genre);
            } else {
                $album->genre = null;
            }

            $album->stored = true;

            return $album;
        } catch (Exception $e) {
            return;
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
     *
     * @return Album|null
     */
    public static function getAlbum($id)
    {
        $db = new Database();

        $db_object = $db->select('*', self::ALBUMS_TABLE, "WHERE `id` = $id");

        if (!isset($db_object[0])) {
            return;
        }

        return self::makeAlbumFromDatabaseObject($db_object[0]);
    }

    /**
     * Returns an array of albums that matches the given title.
     *
     * @param $title string title of the album
     *
     * @return Album[]
     */
    public static function findAlbumByTitle($title)
    {
        $db = new Database();

        $db_objects = $db->select('*', self::ALBUMS_TABLE, "WHERE `title` LIKE '$title'");

        $albums = [];

        if (is_array($db_objects)) {
            foreach ($db_objects as $db_object) {
                $albums[] = self::makeAlbumFromDatabaseObject($db_object);
            }
        }

        return $albums;
    }

    /**
     * Saves or update the song to the database.
     *
     * @return bool
     */
    public function save()
    {
        $db = new Database();

        $arr = [ // I AM A PIRATE!
            'title' => $this->title,
            'genre' => $this->genre,
        ];

        if ($this->stored) {
            return $db->update(self::ALBUMS_TABLE, $arr, "`id` = $this->id");
        } else {
            $status = $db->insert(self::ALBUMS_TABLE, $arr);

            if (!$status) {
                return false;
            }

            $this->id = intval($db->getLastInsertedID());
            $this->stored = true;
        }

        if (!file_exists($this->getAlbumPath()))
            mkdir($this->getAlbumPath(), 0777, true);

        $this->saveJson();

        return true;
    }

    public function saveJson()
    {
        file_put_contents($this->getAlbumPath() . self::DATA_FILE, json_encode($this->toExportableJson(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * @param $source string The source file of the album
     * @param $json string The json containing the metadata of the album
     * @param bool $nestedTracks Are the tracks nested in a CD object?
     * @param bool $removeOnFinish Remove the original directory on finish?
     * @return int id of the album
     * @throws Exception
     */
    public static function importJson($source, $json = null, $nestedTracks = false, $removeOnFinish = false)
    {
        if (!is_dir($source)) {
            throw new Exception("Source is not a directory.");
        }

        if ($json == null) {
            $json = file_get_contents($source . self::DATA_FILE);
        }

        if (empty($json)) {
            throw new InvalidArgumentException('Json not provided.');
        }

        $content = json_decode($json);

        if (empty($content)) {
            throw new Exception(json_last_error_msg());
        }

        if (empty($content->title)) {
            throw new Exception('Album title required.');
        }

        $album = new Album();

        $album->setTitle($content->title);

        if (!$album->save()) {
            throw new Exception('Failed to save the new album to database.');
        }

        $tracks = $nestedTracks ? self::extractTracksFromCd($content->tracks) : $content->tracks;

        $album->addSongs($tracks);

        if (isset($content->cover) && $content->cover != null)
            try {
                $content->cover = FileUtils::normaliseUrl($content->cover);
                $album->setCover($content->cover);
            } catch (Exception $exception) {
                $album->setCover(null);
                error_log('Failed to set cover to album ' . $album->getId() . ' because ' . $exception->getMessage());
            }

        // Let's grab all the juicy stuff.
        if (!FileUtils::moveContents($source, $album->getAlbumPath(), true)) {
            throw new Exception('Failed to move files.');
        }

        // Save the importable json file
        $album->saveJson();

        // Take the garbage out.
        if ($removeOnFinish)
            FileUtils::remove($source, true);

        // Job done, people, let's go home!
        return $album->getId(); // Now just pretend it might return something else, okay?
    }

    /**
     * Flattens a multidimensional [cd][trackNo] array of {@link stdObjects},
     * into a linear queue of ad-hoc defined {@link Song} objects,
     * storing the CD index within the dedicated structure,
     * preparing the aforementioned for being processed by further facilities.
     * <p>
     * Nevertheless, creates artists on-the-fly, if the are missing from the local database,
     * adding a reference inside the {@link Song} object.
     * <p><p>
     * Hot stuff, man.
     *
     * @param $cds array
     *
     * @return Song[]
     */
    private static function extractTracksFromCd($cds)
    {
        $tracks = [];

        foreach ($cds as $cdIndex => $cd_tracks) {
            if (is_array($cd_tracks)) {
                foreach ($cd_tracks as $track) {
                    $track->cd = $cdIndex;
                    $tracks[] = Song::newSongFromJson($track);
                }
            }
        }

        return $tracks;
    }


    /**
     * Updates statistics about the last album played and so on.
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

    /**
     * @return int the last played count
     */
    public static function getLastPlayedId()
    {
        if (file_exists(self::LAST_PLAYED_FILE)) {
            return intval(file_get_contents(self::LAST_PLAYED_FILE));
        }

        return 0;
    }

    /**
     * Increments the count of the last played album.
     *
     * @return int the updated value
     */
    public static function incrementLastPlayedId()
    {
        $last = self::getLastPlayedId() + 1;

        file_put_contents(self::LAST_PLAYED_FILE, $last);

        return $last;
    }

    /**
     * Add Songs to the album and save them.
     * <p>
     * <b>Note: Album must be saved!</b>.
     *
     * @param $songs Song[]
     */
    public function addSongs($songs)
    {
        if (!$this->stored) {
            throw new \RuntimeException('Album must be saved before Songs can be added to.');
        }
        foreach ($songs as $song) {
            $song->setAlbumId($this->getId());
        }

        Song::saveMultiple($songs);
    }

    //<editor-fold desc="Getters and Setters" defaultstate="collapsed">

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Adds a cover to the album.
     *
     * @param $url String either remote or local, the program is so smart it doesn't give flying sheep about it.
     */
    public function setCover($url)
    {
        if ($url === null) {
            if (file_exists($this->getCoverPath())) {
                unlink($this->getCoverPath());
                unlink($this->getThumbPath());
            }
        } else {
            $cover = new Cover($url);

            $cover->saveToAlbum($this->getId());
        }
    }

    /**
     * @return string absolute unix path to cover file.
     */
    public function getCoverPath()
    {
        return $this->albums_root . "$this->id/cover.jpg";
    }

    /**
     * @return string absolute unix path to cover thumb file
     */
    public function getThumbPath()
    {
        return $this->albums_root . "$this->id/cover.jpg";
    }

    /**
     * Returns the number of tracks in the album.
     *
     * @return int the count of tracks
     */
    public function getSongsCount()
    {
        $db = new Database();

        return $db->count(Song::SONGS_TABLE, "album_id = $this->id");
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
     *  Deletes the album and all the related files irreversibly.
     */
    public function delete()
    {
        $database = new Database();

        FileUtils::remove(Config::getPath('albums_root') . $this->id, true);

        foreach ($this->getSongs() as $song) {
            $song->delete();
        }

        return $database->delete(self::ALBUMS_TABLE, "`id` = $this->id");
    }

    /**
     * @return Song[] | null the songs in an album
     */
    public function getSongs()
    {
        return Song::getSongsInAlbum($this->getId());
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
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'artists' => $this->getArtists(),
            'hits' => $this->getHits(),
            'last_played' => $this->getLastPlayed(),
            'cover' => $this->getCoverID()
        ];
    }

    /**
     * @return string A denormalised json that can be used to recreate the album
     */
    public function toExportableJson()
    {
        $array = $this->jsonSerialize();

        $array['artistsNames'] = $this->getArtistsName();
        $array['songs'] = $this->makeSongsExportable($this->getSongs());

        unset($array['id'], $array['cover']);

        return json_encode($array);
    }

    /**
     * @param $songs Song[] the songs to make exportable
     * @return array an associative array containing the exportable songs
     */
    private function makeSongsExportable($songs)
    {
        $exportedSongs = [];

        foreach ($songs as $song) {
            $exportedSong = $song->jsonSerialize();

            unset ($exportedSong['id'], $exportedSong['album_id']);

            $exportedSong['artistsNames'] = Artist::idsToNames($song->getArtistsIds());

            $exportedSongs[] = $exportedSong;
        }

        return $exportedSongs;
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
        $this->title = stripslashes($title);
    }

    /**
     * Return an array of participating artists IDs.
     *
     * @return int []
     */
    public function getArtists()
    {
        $raws = Artist::getArtistIdsInAlbum($this->getId());

        $arr = []; // I'm a pirate!

        foreach ($raws as $raw) {
            $arr[] = intval($raw->artist_id);
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

    /**
     * Returns the timestamp of the last time the cover was edited.
     *
     * @return int|null
     */
    public function getCoverID()
    {
        if (!file_exists($this->getCoverPath())) {
            return;
        }

        return filemtime($this->getCoverPath());
    }

    /**
     * Returns an array of contributing artists.
     *
     * @return string[]
     */
    public function getArtistsName()
    {
        $raws = Artist::getArtistIdsInAlbum($this->getId());

        $arr = []; // I'm a pirate!

        foreach ($raws as $raw) {
            $arr[] = Artist::getArtist(intval($raw->artist_id))->getName();
        }

        return $arr;
    }

    public function getCoverUrl($thumb = false)
    {
        if (!file_exists($this->getCoverPath())) {
            return '/assets/img/album-placeholder.png';
        }

        $file = $thumb ? 'thumb' : 'cover';

        return "/jukebox/$this->id/$file.jpg?" . $this->getCoverID();
    }

    /**
     * Returns an array of songs created with the legacy album system.
     *
     * @return Song[]
     */
    public function getLegacySongs()
    {
        $db = new Database();

        $result = $db->select('tracks', self::ALBUMS_TABLE, 'WHERE `id` = ' . $this->id);

        //$result = $result;

        $songs = json_decode($result[0]->tracks);

        $_songs = [];

        foreach ($songs as $song) {
            $song = Song::newSongFromJson($song, $this->id);
            $_songs[] = $song;
        }

        return $_songs;
    }

    /**
     * The size in MB of the album folder (tracks and covers).
     *
     * @return float|null
     */
    public function getAlbumFolderSize()
    {
        $kb = FileUtils::getDirectorySize($this->albums_root . $this->getId());

        return empty($kb) ? null : $kb / 1000;
    }

    public function getAlbumPath()
    {
        return $this->albums_root . $this->getId() . '/';
    }

    /**
     * Gets an artist from the previous artist configuration and return a new Artist object.
     *
     * @return Artist
     */
    public function getLegacyArtist()
    {
        $db = new Database();

        $result = $db->select('artist', self::ALBUMS_TABLE, 'WHERE `id` = ' . $this->id);

        $artist = $result[0]->artist;

        return Artist::softCreateArtist($artist);
    }

    /**
     * @return int the number of CDs in the album
     */
    public function getCdCount()
    {
        $songs = $this->getSongs();

        if (count($songs) == 0) {
            return 0;
        }

        return end($songs)->getCd();
    }

    //</editor-fold>
}
