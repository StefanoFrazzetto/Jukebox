<?php

/**
 * Class TracksHandler groups the albums/tracks in order to use
 * all the space available on the CD/DVD.
 *
 * @author Stefano Frazzetto <https://github.com/StefanoFrazzetto>
 *
 *  Last update: 2 Apr 2017
 *  Fixed queries to use the new database schema.
 */
use Lib\Config;
use Lib\Database;
use Lib\FileUtils;
use Lib\MusicClasses\Song;
use Lib\StringUtils;

require_once 'autoload.php';

class TracksHandler
{
    private $_database;
    private $_cds;

    /**
     * Create the "Burner" using albums: initialise $DiscWriter and $_albums[ALBUM_ID]['size'];.
     *
     * @param string       $type          the type of compilation to burn (playlist or albums).
     * @param array|string $values        the values passed from the modal (json containing the tracks
     *                                    or an array of album ids).
     * @param string       $output_format the output format for the current cd (mp3 or wav).
     */
    public function __construct($type, $values, $output_format)
    {
        // If the type of operation is not specified, exit with CODE 1.
        if ($type == '') {
            exit(1);
        }

        $this->_disc_writer = new DiscWriter();
        $this->_database = new Database();

        switch ($type) {
            case 'playlist':
                $this->playlist($values);
                break;

            case 'albums':
                file_put_contents('/tmp/tracksHandler.log', "Creating albums cd\n\n", FILE_APPEND);
                $this->manyAlbums($values, $output_format);
                break;

            default:
                exit(1);
        }

        // Save the created array to a tmp file.
        $cds_json = json_encode($this->_cds);
        file_put_contents(BurnerHandler::$_burner_tracks_json, $cds_json);
    }

    /**
     *    Burn a playlist.
     *
     * @param $playlist = json encoded playlist;
     */
    private function playlist($playlist)
    {
        $this->_cds = self::groupTracks($playlist, 'playlist');
    }

    /**
     *    Group the tracks together calculating the size of each group of tracks.
     *
     * @param $tracks
     * @param string $type
     * @param string $output_format
     *
     * @return array
     */
    private function groupTracks($tracks, $type = '', $output_format = '')
    {
        $audio_cd = false;
        $cd_no = 1;
        $cds = [];
        $playlist_index = 1;

        // Check if the user wants to burn an audio CD.
        if ($type == 'albums' && $output_format == 'wav') {
            $audio_cd = true;
        }

        $DiscWriter = new DiscWriter();
        if ($audio_cd) {
            // 80 minutes for audio CD
            $media_size = 80 * 60;
        } else {
            // Whole disc size for MP3
            $media_size = $DiscWriter->getDiscSize();
        }

        foreach ($tracks as $track) {

            // Skip the track if either album or url is not set.
            if (!isset($track['album_id']) || !isset($track['url'])) {
                continue;
            }

            // Updated on 31/10/2016
            // Some files were incorrectly parsed due to special chars
            $track['title'] = sprintf('%02d', $playlist_index).'-'.StringUtils::cleanString($track['title']);

            $playlist_index++;

            $cd_size = &$cds[$cd_no]['size'];

            // If: the track has a CD number different than the $cd_no,
            // it will be put in another "group".
            // Else: if the size of the current group is larger than the media size,
            // the next tracks will be put in $cd_no + 1;

            // Updated on 15/07/2016:
            // If the user is burning an audio CD, the total length of the tracks
            // must be less than 80 minutes.

            $cd_capacity = $media_size - ($media_size * 2) / 100;

            if ($cd_size >= $cd_capacity) {
                $cd_no++;
            } elseif ($audio_cd) {
                $cd_no = $track['cd'];
            }

            $cds[$cd_no][] = $track;

            $conf = new Config();
            $albums_root = $conf->get('paths')['albums_root'];
            $track_path = $albums_root.$track['album_id'].'/'.$track['url'];

            if (!isset($cd_size)) {
                $cd_size = 0;
            }

            if ($audio_cd) {
                $cd_size += FileUtils::getTrackLength($track_path);
            } else {
                $cd_size += FileUtils::getFileSize($track_path);
            }
        }

        return $cds;
    }

    /**
     *    Burn many albums at once.
     *
     * @param array  $albums        the array containing the album ids.
     * @param string $output_format the CD output format (mp3 or wav).
     */
    private function manyAlbums($albums, $output_format)
    {
        $tracks = [];

        foreach ($albums as $album_id) {
            $album_path = BurnerHandler::$_burner_folder.'/'.$album_id;
            $size = FileUtils::getDirectorySize($album_path);
            $Songs = Song::getSongsInAlbum($album_id);

            if ($size === 0 || $Songs == null) {
                continue;
            }

            $tmp_array = [];
            foreach ($Songs as $song) {
                $song_id = $song->getId();
                $tmp_array[$song_id] = ([
                    'album_id' => $album_id,
                    'title'    => $song->getTitle(),
                    'url'      => $song->getUrl(),
                    'cd'       => $song->getCd(),
                ]);
            }

            $tracks = array_merge($tracks, $tmp_array);
        }

        $this->_cds = self::groupTracks($tracks, 'albums', $output_format);
    }

    public static function getTracksJSON()
    {
        $content = file_get_contents(BurnerHandler::$_burner_tracks_json);
        if ($content === false) {
            return;
        }

        return json_decode($content, true);
    }

    public static function removeBurnedTracksJSON($index)
    {
        $cds_json = json_decode(BurnerHandler::$_burner_tracks_json, true);
        unset($cds_json[$index]);
        file_put_contents(BurnerHandler::$_burner_tracks_json, $cds_json);
    }

    public function getRequiredCDS()
    {
        return count($this->_cds);
    }

    public function getCompilationSize()
    {
        $indexes = array_filter(array_keys($this->_cds), 'is_int');

        $index = (int) $indexes[0];

        return $this->_cds[$index]['size'] / 1000;
    }
}
