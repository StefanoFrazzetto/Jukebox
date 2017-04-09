<?php

namespace Providers;

use Exception;
use InvalidArgumentException;

// Set the user agent for the APIs
ini_set('user_agent', 'Jukebox/1.0.0 (info.freelancewd@gmail.com)');

/**
 * Class MusicBrainz retrieves a CD/DVD information using MusicBrainz API v2.
 *
 * @author Stefano Frazzetto <https://github.com/StefanoFrazzetto>
 *
 * @version 2.0.1
 *
 * @see http://musicbrainz.org/doc/Development/XML_Web_Service/Version_2
 */
class MusicBrainz
{
    /** @var string the discid obtained from a CD/DVD */
    private $disc_id;

    /** @var string the raw json from MusicBrainz */
    private $json;

    private $info;

    /** @var string the CD/DVD title */
    private $title;

    private $numberOfTracks;

    /** @var array the information about the CD/DVD */
    private $release_id;

    /** @var array the array containing the tracks */
    private $tracks;

    /**
     * MusicBrainz constructor tries to get the information about a disc id
     * using MusicBrainz v2 APIs.
     *
     * @param string $disc_id The disc id to use when performing the research.
     *
     * @throws InvalidArgumentException If it was not possible to get the information from MusicBrainz.
     * @throws Exception                If it was not possible to contact MusicBrainz.
     */
    public function __construct($disc_id)
    {
        if (empty($disc_id)) {
            throw new InvalidArgumentException('You must provide a disc id.');
        }

        $this->disc_id = $disc_id;
        $query = "http://musicbrainz.org/ws/2/discid/$disc_id?inc=recordings+artist-credits&fmt=json";

        $this->json = @file_get_contents($query);
        if ($this->json === false) {
            throw new Exception('Error while contacting the MusicBrainz API: '.$this->json);
        }

        $this->info = json_decode($this->json, true);
        if ($this->info === false || $this->info === null) {
            throw new Exception('No information provided from MusicBrainz: '.$this->info);
        }

        if (isset($this->info['error'])) {
            throw new InvalidArgumentException($this->info['error']);
        }

        $this->numberOfTracks = $this->info['offset-count'];
        $release = $this->info['releases'][0];
        $this->title = $release['title'];
        $this->release_id = $release['id'];
        $this->tracks = $release['media'][0]['tracks'];
    }

    /**
     * Returns the CD/DVD title if found, empty string otherwise.
     *
     * @return string The CD/DVD title if found, empty string otherwise.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns the release id if found, empty string otherwise.
     *
     * @return string The release ID if found, empty string otherwise.
     */
    public function getReleaseID()
    {
        return $this->release_id;
    }

    /**
     * Returns the array containing the tracks.
     *
     * @return array The array containing the tracks information.
     */
    public function getTracks()
    {
        return $this->tracks;
    }

    /**
     * Returns an array containing the parsed tracks information.
     *
     *
     * Each track will have title, length, one or more artists,
     * and its number from the source media.
     *
     * The returned array is not 0-indexed, so the key will correspond to
     * the actual track number from its source.
     *
     * @return array the parsed tracks array.
     */
    public function getParsedTracks()
    {
        if (!is_array($this->tracks) || count($this->tracks) <= 0) {
            return array();
        }

        $temp_tracks = [];
        foreach ($this->tracks as $track) {
            $artists_names = [];
            $artist_credit = $track['artist-credit'];
            foreach ($artist_credit as $artist) {
                $artists_names[] = $artist['name'];
            }

            $recording = $track['recording'];
            $temp_tracks[$track['number']] = [
                'number'  => intval($track['number']),
                'title'   => $recording['title'],
                'artists' => $artists_names,
                'length'  => $recording['length'],
            ];
        }

        return $temp_tracks;
    }

    public function getJson()
    {
        return $this->json;
    }

    public function getInfo()
    {
        return $this->info;
    }
}
