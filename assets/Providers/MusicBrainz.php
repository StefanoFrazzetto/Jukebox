<?php

// Set the user agent for the APIs
ini_set('user_agent', 'Jukebox/1.0.0 (info.freelancewd@gmail.com)');

/**
 * Class MusicBrainz retrieves a CD/DVD information using MusicBrainz API v2.
 *
 * @author Stefano Frazzetto <https://github.com/StefanoFrazzetto>
 * @version 2.0.0
 * @see http://musicbrainz.org/doc/Development/XML_Web_Service/Version_2
 * @licence GNU AGPL v3 - https://www.gnu.org/licenses/agpl-3.0.txt
 */
class MusicBrainz
{
    /** @var string the discid obtained from a CD/DVD */
    private $disc_id;

    /** @var string the raw json from MusicBrainz */
    private $json;

    private $info;

    /** @var  string the CD/DVD title */
    private $title;

    private $numberOfTracks;

    /** @var  array the information about the CD/DVD */
    private $release_id;

    /** @var  array the array containing the tracks */
    private $tracks;

    /**
     * MusicBrainz constructor tries to get the information about a disc id
     * using MusicBrainz v2 APIs.
     *
     * @param string $disc_id The disc id to use when performing the research.
     * @throws Exception If it was not possible to get the information from MusicBrainz.
     */
    public function __construct($disc_id)
    {
        $this->disc_id = $disc_id;
        $query = "http://musicbrainz.org/ws/2/discid/$disc_id?inc=recordings+artist-credits&fmt=json";
        //       $query = "http://musicbrainz.org/ws/2/discid/$disc_id?inc=aliases+recordings&fmt=json";
//        http://musicbrainz.org/ws/2/discid/SHexqDpVuXZ5oQEVXnZviX2e9OA-?inc=recordings+artist-credits&fmt=json
        $this->json = file_get_contents($query);
        $this->info = json_decode($this->json, true);

        if ($this->json !== false && $this->info !== null) {
//            throw new Exception('Error occurred while trying to get the disc id information.');
            $this->numberOfTracks = $this->info['offset-count'];

            $release = $this->info['releases'][0];

            $this->title = $release['title'];
            $this->release_id = $release['id'];
            $this->tracks = $release['media'][0]['tracks'];
        }


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
     * Parse the tracks information and stores their TITLE, NUMBER and LENGTH.
     *
     * @return array - the parsed tracks array.
     */
    public function getParsedTracks()
    {
        if (!is_array($this->tracks) || count($this->tracks) <= 0) {
            return null;
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
                'title' => $recording['title'],
                'length' => $recording['length'],
                'number' => intval($track['number']),
                'artists' => $artists_names
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