<?php

namespace Lib;

use InvalidArgumentException;

/**
 * Class ID3 is used to get the main ID3v2 tags from a specified file.
 *
 * @see http://id3.org/id3v2.3.0
 *
 * @author Stefano Frazzetto <https://github.com/StefanoFrazzetto>
 *
 * @version 1.0.0
 */
class ID3
{
    /** @var array The array containing the ID3 tags */
    private $tags;

    private $has_tags = false;

    /**
     * ID3 constructor extracts all the information from the specified file.
     *
     * If no ID3 tags are found, has_tags is set to false and the constructor
     * returns.
     *
     * @param $file_path string the path to the file
     *
     * @throws InvalidArgumentException if the specified file path is not valid.
     */
    public function __construct($file_path)
    {
        if (!file_exists($file_path)) {
            throw new InvalidArgumentException('You must specify a valid file path.');
        }

        $output = OS::execute("id3v2 -R $file_path");

        $count = preg_match_all('/^([a-zA-Z]*[0-3]?):\\s*(.*)$/m', $output, $matches);

        $this->tags = [];
        if ($count > 2) {
            $results = [];
            foreach ($matches[1] as $key => $match) {
                if (!in_array($match, ['PRIV', 'COMM'])) {
                    $results[$match] = $matches[2][$key];
                }
            }

            $this->tags = $results;

            // If the track ID3 has the title, we assume it has ID3 tags.
            if (!empty($this->getTitle())) {
                $this->has_tags = true;
            }

            // Parses the "Part of a set" string
            if (isset($this->tags['TPOS'])) {
                $sets = $this->tags['TPOS'];
                if (preg_match("/([0-9]+)\/([0-9]+)/", $sets, $setsMatches)) {
                    $this->tags['TPOS1'] = intval($setsMatches[1][0]);
                    $this->tags['TPOS2'] = intval($setsMatches[2][0]);
                }
            }
        }
    }

    /**
     * The 'Title/Songname/Content description' frame is the actual name of the
     * piece (e.g. "Adagio", "Hurricane Donna").
     *
     * @return string The song title
     */
    public function getTitle()
    {
        return $this->tags['TIT2'];
    }

    /**
     * Returns true if the track contains ID3 tags, otherwise false.
     *
     * @return bool
     */
    public function hasTags()
    {
        return $this->has_tags;
    }

    /**
     * The 'Content group description' frame is used if the sound belongs to a
     * larger category of sounds/music. For example, classical music is often
     * sorted in different musical sections (e.g. "Piano Concerto",
     * "Weather - Hurricane").
     *
     * @return string The content group description
     */
    public function getContentGroupDescription()
    {
        return $this->tags['TIT1'];
    }

    /**
     * The 'Subtitle/Description refinement' frame is used for information directly
     * related to the contents title (e.g. "Op. 16" or "Performed live at Wembley").
     *
     * @return string The song title
     */
    public function getSubtitle()
    {
        return $this->tags['TIT3'];
    }

    /**
     * The 'Language(s)' frame should contain the languages of the text or lyrics
     * spoken or sung in the audio. The language is represented with three
     * characters according to ISO-639-2. If more than one language is used
     * in the text their language codes should follow according to their usage.
     *
     * @return string The languages of the text/lyrics spoken or sung in the audio
     */
    public function getLanguage()
    {
        return $this->tags['TLAN'];
    }

    /**
     * The 'Original filename' frame contains the preferred filename for the file,
     * since some media does not allow the desired length of the filename.
     * The filename is case sensitive and includes its suffix.
     *
     * @return string The preferred filename for the file
     */
    public function getFileName()
    {
        return $this->tags['TOFN'];
    }

    /**
     * The 'Track number/Position in set' frame is a numeric string containing
     * the order number of the audio-file on its original recording. This may be
     * extended with a "/" character and a numeric string containing the total
     * numer of tracks/elements on the original recording. E.g. "4/9".
     *
     * @return int The number corresponding to the order number of the
     *             audio-file on its original recording converted to int
     */
    public function getTrackNumber()
    {
        return intval($this->tags['TRCK']);
    }

    /**
     * The 'Part of a set' frame is a numeric string that describes which part
     * of a set the audio came from. This frame is used if the source described
     * in the "TALB" frame is divided into several mediums, e.g. a double CD.
     * The value may be extended with a "/" character and a numeric string
     * containing the total number of parts in the set. E.g. "1/2".
     *
     * @return string The numeric string that describes which part of a set the
     *                audio came from
     */
    public function getSet()
    {
        return $this->tags['TPOS'];
    }

    /**
     * The number associated with the current set.
     *
     * @see getSetNumber()
     *
     * @return int The number associated with the current set
     */
    public function getSetNumber()
    {
        return $this->tags['TPOS1'];
    }

    /**
     * The total number of sets for the album.
     *
     * @see getSetNumber()
     *
     * @return int The total number of sets
     */
    public function getSetTotal()
    {
        return $this->tags['TPOS2'];
    }

    /**
     *The 'Content type', which previously was stored as a one byte numeric
     * value only, is now a numeric string. You may use one or several of
     * the types as ID3v1.1 did or, since the category list would be
     * impossible to maintain with accurate and up to date categories,
     * define your own.
     * References to the ID3v1 genres can be made by, as first byte,
     * enter "(" followed by a number from the genres list (appendix A)
     * and ended with a ")" character. This is optionally followed by a
     * refinement, e.g. "(21)" or "(4)Eurodisco". Several references can
     * be made in the same frame, e.g. "(51)(39)". If the refinement should
     * begin with a "(" character it should be replaced with "((", e.g.
     * "((I can figure out any genre)" or "(55)((I think...)". The
     * following new content types is defined in ID3v2 and is implemented
     * in the same way as the numerig content types, e.g. "(RX)".
     *
     * @return string The song genre
     */
    public function getGenre()
    {
        return $this->tags['TCON'];
    }

    /**
     * The 'Year' frame is a numeric string with a year of the recording.
     * This frames is always four characters long (until the year 10000).
     *
     * @return int The year of the recording converted to int
     */
    public function getYear()
    {
        return intval($this->tags['TYER']);
    }

    /**
     * The 'Album/Movie/Show title' frame is intended for the title of the
     * recording(/source of sound) which the audio in the file is taken from.
     *
     * @return string The title of the recording which the audio is taken from
     */
    public function getAlbum()
    {
        return $this->tags['TALB'];
    }

    /**
     * The 'Lead artist(s)/Lead performer(s)/Soloist(s)/Performing group' is
     * used for the main artist(s). They are separated with the "/" character.
     *
     * @return string The main artist(s)
     */
    public function getLeadArtist()
    {
        return $this->tags['TPE1'];
    }

    /**
     * The 'Band/Orchestra/Accompaniment' frame is used for additional
     * information about the performers in the recording.
     *
     * @return string The band/orchestra name
     */
    public function getBand()
    {
        return $this->tags['TPE2'];
    }

    /**
     * The 'Conductor' frame is used for the name of the conductor.
     *
     * @return string The name of the conductor
     */
    public function getConductor()
    {
        return $this->tags['TPE3'];
    }

    /**
     * The 'Publisher' frame simply contains the name of the label or publisher.
     *
     * @return string The name of the label or publisher
     */
    public function getPublisher()
    {
        return $this->tags['TPUB'];
    }
}
