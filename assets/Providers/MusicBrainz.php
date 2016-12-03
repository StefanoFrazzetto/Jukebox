<?php

/**
 * Class MusicBrainz retrieves a CD/DVD tracks information using
 * MusicBrainz API v2.
 *
 * @author Stefano Frazzetto - https://github.com/StefanoFrazzetto
 * @see http://musicbrainz.org/doc/Development/XML_Web_Service/Version_2
 * @version 1.0.0
 * @licence GNU AGPL v3 - https://www.gnu.org/licenses/agpl-3.0.txt
 */
class MusicBrainz
{
    private $disc_id;
    private $res_array;

    private $title;
    private $release_id;
    private $raw_tracks;
    private $tracks;

    /**
     * MusicBrainz constructor.
     * @param $disc_id - the disc id to use when performing the research.
     */
    public function __construct($disc_id)
    {
        $this->disc_id = $disc_id;
        $query = "http://musicbrainz.org/ws/2/discid/$disc_id?inc=aliases+recordings&fmt=json";
        $json = @file_get_contents($query);
        $this->res_array = json_decode($json);

        if ($json !== FALSE && $this->res_array != NULL) {
            $release = $this->res_array->releases[0];

            // Store the CD/DVD title.
            $this->title = $release->title;

            // Store the release id.
            $this->release_id = $release->id;

            // Parse and store the tracks.
            $this->raw_tracks = $release->media[0]->tracks;
        }
    }

    /**
     * Parses the tracks information and stores their TITLE, NUMBER and LENGTH.
     *
     * @return array - the parsed tracks array.
     */
    private function parseTracksInfo()
    {
        $temp_tracks = [];
        if (is_array($this->raw_tracks) && count($this->raw_tracks) > 0) {
            foreach ($this->raw_tracks as $temp) {
                $temp_tracks[$temp->number] = array('title' => $temp->recording->title, 'number' => $temp->number, 'length' => $temp->recording->length);
            }
        }
        return $temp_tracks;
    }

    /**
     * Returns the CD/DVD title if found, empty string otherwise.
     *
     * @return string - the CD/DVD title if found, empty string otherwise.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns the release id if found, empty string otherwise.
     *
     * @return string - the release ID if found, empty string otherwise.
     */
    public function getReleaseID()
    {
        return $this->release_id;
    }

    /**
     * Returns the array containing the tracks information.
     * If the parameter passed is <b>TRUE</b>, the returned array will contain only the tracks TITLE, NUMBER and LENGTH.
     * If the parameter is <b>FALSE</b>, the array will contain all the information from MusicBrainz.
     *
     * @param boolean - true to get a parsed array, false to get all the info from MusicBrainz.
     * @return array - the array containing the tracks information.
     */
    public function getTracks($return_parsed = true)
    {
        if ($return_parsed) {
            return $this->parseTracksInfo();
        }

        return $this->tracks;
    }


}