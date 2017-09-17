<?php

namespace Lib;

use Exception;
use Exceptions\UploadException;
use InvalidArgumentException;
use Lib\MusicClasses\Album;
use Providers\MusicBrainz;
use Symfony\Component\Finder\Finder;

/**
 * Class Uploader handles the upload of albums into the jukebox.
 *
 * @author Stefano Frazzetto <https://github.com/StefanoFrazzetto>
 *
 * @version 1.0.0
 */
class Uploader
{
    /** @const the uploader source when using the ripper */
    const MEDIA_SOURCE_RIPPER = 'ripper';

    /** @const the uploader source when using files */
    const MEDIA_SOURCE_FILES = 'files';

    /** @const string the status in case of success */
    const STATUS_SUCCESS = 'success';

    /** @const string the default status */
    const STATUS_IDLE = 'idle';

    /** @const string the status in case of error */
    const STATUS_ERROR = 'error';

    /** @const string the status when the uploader is working */
    const STATUS_UPLOADING = 'uploading';

    /** @const string the status file name */
    const STATUS_FILE = 'uploader_status.json';

    /** @const array the array of allowed music extensions */
    const ALLOWED_MUSIC_EXTENSIONS = ['mp3', 'wav'];

    /** @var int the uploader source */
    private $source = 2;

    /** @var string the uploader session id */
    private $uploader_id;

    private $music_brainz_info;

    private $album_title;

    private $release_id;

    public function __construct($media_source = '')
    {
        $this->source = $media_source;
    }

    /**
     * @return string the upload status.
     */
    public static function getStatus()
    {
        $config_path = self::getPath();
        $content = json_decode($config_path . self::STATUS_FILE, true);

        return isset($content['status']) ? $content['status'] : self::STATUS_IDLE;
    }

    /**
     * @return string the path to the temp folder
     */
    public static function getPath()
    {
        $config = new Config();

        return $config->get('paths')['uploader'];
    }

    /**
     * Sets the current status in the status file.
     *
     * @param string $status the uploader status
     *
     * @return bool true on success, false on failure.
     */
    public static function setStatus($status)
    {
        $data['status'] = $status;
        $status_file = self::getPath() . self::STATUS_FILE;

        return FileUtils::writeJson($data, $status_file);
    }

    /**
     * @return string the upload status.
     */
    public static function getUploaderId()
    {
        $config_path = self::getPath();
        $content = json_decode($config_path . self::STATUS_FILE, true);

        return isset($content['uploader_id']) ? $content['uploader_id'] : null;
    }

    /**
     * Sets the session id into the status file.
     *
     * @param string $uploader_id the uploader id.
     *
     * @return bool true on success, false on failure.
     */
    public static function setUploaderId($uploader_id)
    {
        $data['uploader_id'] = $uploader_id;
        $status_file = self::getPath() . self::STATUS_FILE;

        return FileUtils::writeJson($data, $status_file);
    }

    /**
     * Uploads one or more files into the specified directory.
     *
     * @param string $uploadFolderID The destination directory.
     * @param null | integer $cd defines in which cd the file should be saved, when null the id3 is used or 1.
     *
     * @throws InvalidArgumentException if no argument is passed.
     * @throws UploadException          if the file was not uploaded or if the
     *                                  extension of the file is not allowed.
     *
     * @return bool true if the operation succeeds, false otherwise.
     */
    public static function upload($uploadFolderID, $cd = null)
    {
        if (empty($uploadFolderID)) {
            throw new InvalidArgumentException('The upload folder ID must not be empty.');
        }

        if (!isset($_FILES['file'])) {
            throw new UploadException(UPLOAD_ERR_NO_FILE);
        }

        // Check if the file was uploaded
        if ($_FILES['file']['error'] != 0) {
            throw new UploadException($_FILES['file']['error']);
        }

        $file_extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $file_extension = strtolower($file_extension);

        // Check allowed extensions
        $allowed_extensions = array_merge(Cover::ALLOWED_COVER_EXTENSIONS, self::ALLOWED_MUSIC_EXTENSIONS);
        if (!in_array($file_extension, $allowed_extensions)) {
            throw new UploadException(UPLOAD_ERR_EXTENSION);
        }

        $file_name = StringUtils::cleanString($_FILES['file']['name']);
        $source_file = $_FILES['file']['tmp_name'];

        $destination_path = self::getPath() . $uploadFolderID;

        // If a track is uploaded, attempts to store it in the correct cd.
        if (in_array($file_extension, self::ALLOWED_MUSIC_EXTENSIONS)) {
            // If the CD is null we will opt for an auto-mode that chooses the cd.
            if (empty($cd) && $cd < 1) {
                $id3 = new ID3($_FILES['file']['tmp_name']);

                $cd = @$id3->getSetNumber();

                if (empty($cd)) {
                    $cd = 1;
                }
            }

            $destination_path .= "/CD$cd";
        }

        // Check if the destination directory exists
        if (!is_dir($destination_path)) {
            mkdir($destination_path, 0777, true);
        }

        $destination_file = $destination_path . '/' . $file_name;

        return move_uploaded_file($source_file, $destination_file);
    }

    /**
     * Returns a new upload ID and create its directory.
     *
     * If the uploader ID already exists, the function
     * calls itself.
     *
     * @return string the current uploader ID.
     */
    public static function getNewUploaderID()
    {
        self::removeOldDirectories();

        $id = uniqid('', true);

        // Check if the folder already exists
        $dir = Config::getPath('uploader') . "$id";
        if (file_exists($dir)) {
            return self::getNewUploaderID();
        }

        mkdir($dir, 0755, true);

        return $id;
    }

    /**
     * Removes all the uploader directories older than 4 hours.
     */
    private static function removeOldDirectories()
    {
        $hoursInMinutes = 4 * 60;

        try {
            // Delete old uploader directories
            FileUtils::deleteDirectoriesOlderThan(static::getPath(), $hoursInMinutes);
        } catch (InvalidArgumentException $e) {
            // Should never happen.
        }
    }

    /**
     * Returns the current open upload sessions.
     *
     * @return array|null the array containing the upload sessions IDs.
     */
    public static function getUploadsInProgress()
    {
        $upload_path = Config::getPath('uploader');
        $directories = FileUtils::getDirectories($upload_path);

        return $directories;
    }

    /**
     * Creates a status array from a status, a message, and an optional
     * response code.
     *
     * @param string $status the status
     * @param string $message the message
     * @param int $response_code the response code to use in case of error
     *
     * @return array containing the status and the message.
     */
    public static function createStatus($status, $message = '', $response_code = 200)
    {
        $return = [
            'status' => $status,
            'message' => $message,
        ];

        if ($status == self::STATUS_ERROR) {
            http_response_code($response_code);
        }

        return $return;
    }

    /**
     * Creates an album using a string containing a correctly formatted json.
     * <p>
     * Returns the ID if the album has been successfully created.
     *
     * @param string $json the json to be parsed which contains
     *                     the album info.
     * @param $uploader_id string uploader id
     *
     * @throws Exception if the content was not valid
     *
     * @return int|null the album id if successful, null if failed
     */
    public function createAlbumFromJson($json, $uploader_id)
    {
        $source = self::getPath() . $uploader_id . '/';

        return Album::importJson($source, $json, true, true);
    }

    /**
     * Returns an array containing the album info.
     *
     * If the tracks were uploaded using the ripper, the info is
     * retrieved using the disc_id associated with the disc, then
     * MusicBrainz is used to retrieve the necessary info.
     *
     * If the tracks were uploaded from any other source,
     * this method will try to get any info using the tracks
     * ID3 tags.
     *
     * Ultimately, if no ID3 tags are found, the returning array
     * will containing just the basic info about the file, such as
     * title, url, length, number, and an empty array of artists.
     *
     * @param string $uploader_id the uploader id associated with
     *                            the directory containing the tracks.
     *
     * @throws InvalidArgumentException if either the path or the media
     *                                  is empty.
     *
     * @return array the array containing the album information.
     */
    public function getAlbumInfo($uploader_id)
    {
        if (empty($uploader_id)) {
            throw new InvalidArgumentException('You must provide a valid uploader id.');
        }

        $this->uploader_id = $uploader_id;
        $tracks_info = $this->getTracksInfo();
        $cover_info = $this->getCoverInfo();

        $cover = isset($cover_info[0]) ? $cover_info[0] : null;

        $info = [
            'title' => $this->getAlbumTitle(),
            'titles' => [],
            'tracks' => $tracks_info,
            'cover' => $cover,
            'covers' => $cover_info,
        ];

        return $info;
    }

    private function getTracksInfo()
    {
        $tracks_info = [];
        $full_path = self::getPath() . $this->uploader_id;

        $cdFinder = new Finder();
        $cds = $cdFinder->in($full_path)->directories()->sortByName();

        foreach ($cds as $cd) {
            $re = '/[0-9]+$/';
            preg_match_all($re, $cd, $matches, PREG_SET_ORDER, 0);
            $cdNo = $matches[0][0];

            $finder = new Finder();
            $tracks = $finder->in($full_path . "/CD$cdNo/")->files()->name('/^.*\.(mp3|wav)$/i')->sortByName();

            if ($this->source == static::MEDIA_SOURCE_RIPPER) { // If the source is the ripper
                $tracks_info["CD$cdNo"] = $this->createTracksInfoMusicBrainz($tracks);
            } else { // If the source is just files
                $tracks_info["CD$cdNo"] = $this->createTracksInfoFromFiles($tracks);
            }
        }

        return $tracks_info;
    }

    private function createTracksInfoMusicBrainz($tracks)
    {
        $index = 1;
        $tracks_info = [];
        $music_brainz_info = $this->getMusicBrainzInfo();

        foreach ($tracks as $track) {
            if (isset($music_brainz_info[$index])) {
                $title = $music_brainz_info[$index]['title'];
                $artists = $music_brainz_info[$index]['artists'];
            } else {
                $title = "Track $index";
                $artists = [];
            }

            $tracks_info[] = [
                'number' => $index,
                'title' => $title,
                'url' => basename($track),
                'length' => FileUtils::getTrackLength($track),
                'artists' => $artists,
            ];

            $index++;
        }

        return $tracks_info;
    }

    private function getMusicBrainzInfo()
    {
        if (empty($this->music_brainz_info)) {
            $disc_id = new DiscRipper();
            $disc_id = $disc_id->getDiscID();

            try {
                $music_brainz = new MusicBrainz($disc_id);
                $this->music_brainz_info = $music_brainz->getParsedTracks();
                $this->album_title = $music_brainz->getTitle();
                $this->release_id = $music_brainz->getReleaseID();
            } catch (Exception $x) {
                // It was not possible to get any information from MusicBrainz.
                $this->music_brainz_info = [];
            }
        }

        return $this->music_brainz_info;
    }

    /**
     * Create the array containing the tracks information.
     *
     * The method first tries to retrieve the information using the ID3
     * tags. If ID3 is not available in the track, then basic information
     * from the file is retrieved.
     *
     * @param Finder $tracks the array of tracks paths
     *
     * @return array containing the tracks information
     */
    private function createTracksInfoFromFiles($tracks)
    {
        $tracks_info = [];
        $index = 1;

        foreach ($tracks as $track) {
            $id3 = new ID3($track);
            if ($id3->hasTags()) { // If the tracks have id3 tags

                if (empty($this->album_title)) {
                    $this->album_title = $id3->getAlbum();
                }

                $tracks_info[] = [
                    'number' => $id3->getTrackNumber(),
                    'title' => $id3->getTitle(),
                    'url' => basename($track),
                    'length' => FileUtils::getTrackLength($track),
                    'artists' => [$id3->getLeadArtist()],
                ];
            } else { // Get basic information from the files

                // Create the track title
                if (StringUtils::contains($track, '.mp3')) {
                    $title = basename($track, '.mp3');
                } elseif (StringUtils::contains($track, '.wav')) {
                    $title = basename($track, '.wav');
                } else {
                    $title = basename($track);
                }

                $tracks_info[] = [
                    'number' => $index++,
                    'title' => $title,
                    'url' => basename($track),
                    'length' => FileUtils::getTrackLength($track),
                    'artists' => [],
                ];
            }
        }

        return $tracks_info;
    }

    /**
     * @return string[]
     */
    private function getCoverInfo()
    {
        $tracks_info = [];
        $full_path = self::getPath() . $this->uploader_id;

        $finder = new Finder();

        $pack = function ($ext, $finder, $full_path, &$tracks_info) {
            /** @noinspection PhpUndefinedMethodInspection */
            $covers = $finder->in($full_path)->files()->name($ext)->sortByName();
            foreach ($covers as $track) {
                /* @noinspection PhpUndefinedMethodInspection */
                $tracks_info[] = FileUtils::pathToHostUrl($track->getRealPath());
            }
        };

        $pack('*.jpg', $finder, $full_path, $tracks_info);
        $pack('*.png', $finder, $full_path, $tracks_info);
        $pack('*.jpeg', $finder, $full_path, $tracks_info);

        // I have no idea why but the same file was repeated 5 times. This ugly fix should do.
        $tracks_info = array_unique($tracks_info);

        return $tracks_info;
    }

    private function getAlbumTitle()
    {
        return $this->album_title;
    }
}
