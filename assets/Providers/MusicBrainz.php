<?php

/**
 * Class MusicBrainz retrieves a CD/DVD tracks information using
 * MusicBrainz API.
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

        if ($this->res_array != NULL) {
            $release = $this->res_array->releases[0];

            // Store the CD/DVD title.
            $this->title = $release->title;

            // Store the release id.
            $this->release_id = $release->id;

            // Parse and store the tracks.
            $this->raw_tracks = $release->media[0]->tracks;
            $this->parseTracksInfo($this->raw_tracks);
        }
    }

    /**
     * Parses the tracks information and stores their TITLE, NUMBER and LENGTH.
     *
     * @param $tracks - the raw tracks array from MusicBrainz.
     */
    private function parseTracksInfo($tracks)
    {
        if (is_array($tracks)) {
            foreach (@$tracks as $temp) {
                $this->tracks[$temp->number] = array('title' => $temp->recording->title, 'number' => $temp->number, 'length' => $temp->recording->length);
            }
        }
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
     * @return string - the release id if found, empty string otherwise.
     */
    public function getReleaseID()
    {
        return $this->release_id;
    }

    /**
     * Returns the array containing the tracks parsed info: TITLE, NUMBER, LENGTH.
     *
     * @return array - the array containing TITLE, NUMBER and LENGTH of each track.
     */
    public function getTracks()
    {
        return $this->tracks;
    }

}