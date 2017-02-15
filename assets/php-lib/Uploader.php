<?php

namespace Lib;

use Exception;
use InvalidArgumentException;
use Providers\MusicBrainz;
use Symfony\Component\Finder\Finder;
use UploadException;

/**
 * Class Uploader handles the upload of albums into the jukebox.
 */
class Uploader
{
    /** @const the uploader source when using the ripper */
    const MEDIA_SOURCE_RIPPER = 'ripper';

    /** @const the uploader source when using files */
    const MEDIA_SOURCE_FILES = 'files';

    /** @const string the status in case of success */
    const STATUS_SUCCESS = 'success';

    /** @const string the status in case of error */
    const STATUS_ERROR = 'error';

    /** @const string the status file name */
    const STATUS_FILE = "uploader_status.json";

    /** @const array the array of allowed music extensions */
    const ALLOWED_MUSIC_EXTENSIONS = ['mp3'];

    /** @var string the uploader temp directory */
    private $tmp_path;

    /** @var int the uploader source */
    private $source = 2;

    public function __construct($media_source = "")
    {
        $this->source = $media_source;

        $this->tmp_path = self::getPath();
    }

    /**
     * @return string the path to the temp folder
     */
    private static function getPath()
    {
        $config = new Config();
        return $config->get("paths")["uploader"];
    }

    /**
     * @return string JSON that indicates the status of the upload.
     */
    public static function getStatus()
    {
        $config_path = self::getPath();
        return json_encode($config_path . self::STATUS_FILE);
    }

    /**
     * Sets the current status in the status file.
     *
     * @param string $json The JSON file to be saved
     */
    public static function setStatus($json)
    {
        if (empty($json)) {
            throw new InvalidArgumentException("The parameter must not be empty.");
        }

        $status_file = self::getPath() . self::STATUS_FILE;
        if (!file_exists($status_file)) {
            file_put_contents($status_file, '');
        }


    }

    /**
     * Uploads one or more files into the specified directory.
     *
     * @param string $uploadFolderID The destination directory
     * @return bool true if the operation succeeds, false otherwise.
     *
     * @throws InvalidArgumentException if no argument is passed.
     *
     * @throws UploadException if the file was not uploaded or if the
     * extension of the file is not allowed.
     */
    public static function upload($uploadFolderID)
    {
        if (empty($uploadFolderID)) {
            throw new InvalidArgumentException("The upload folder ID must not be empty.");
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

        // Check if the destination directory exists
        $destination_path = self::getPath() . $uploadFolderID;
        if (!is_dir($destination_path)) {
            mkdir($destination_path, 0777, true);
        }

        $destination_file = $destination_path . '/' . $file_name;

        return move_uploaded_file($source_file, $destination_file);
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
    public static function createStatus($status, $message = "", $response_code = 200)
    {
        $return = [
            'status' => $status,
            'message' => $message
        ];

        if ($status == self::STATUS_ERROR) {
            http_response_code($response_code);
        }

        return $return;
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
        $id = uniqid("", true);

        // Check if the folder already exists
        $dir = Config::getPath('uploader') . "$id";
        if (file_exists($dir)) {
            return self::getNewUploaderID();
        }

        mkdir($dir, 0755, true);
        return $id;
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
     * Returns an array containing the tracks info.
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
     * the directory containing the tracks.
     *
     * @throws InvalidArgumentException if either the path or the media
     * is empty.
     *
     * @return array the array containing the tracks information.
     */
    public function getTracksInfo($uploader_id)
    {
        if (empty($uploader_id)) {
            throw new InvalidArgumentException('You must provide the tracks path.');
        }

        $tracks_info = [];
        $full_path = Uploader::getPath() . $uploader_id;

        $finder = new Finder();
        $tracks = $finder->in($full_path)->files()->sortByName();

        if ($this->source == static::MEDIA_SOURCE_RIPPER) { // If the source is the ripper
            $disc_id = new DiscRipper();
            $disc_id = $disc_id->getDiscID();

            try {
                $music_brainz = new MusicBrainz($disc_id);
                $tracks_info = $music_brainz->getParsedTracks();
                return $this->createTracksInfoMusicBrainz($tracks, $tracks_info);
            } catch (InvalidArgumentException $e) {
                // The disc id is wrong.
            } catch (Exception $x) {
                // It was not possible to get any information from MusicBrainz.
            }
        }

        if ($this->source == static::MEDIA_SOURCE_FILES) { // If the source is just files
            $tracks_info = $this->createTracksInfoFromID3($tracks);
        }

        if (empty($tracks_info)) {
            $tracks_info = $this->createTracksInfoFromFiles($tracks);
        }

        return $tracks_info;
    }

    private function createTracksInfoMusicBrainz($tracks, $music_brainz_info)
    {
        $index = 1;
        $tracks_info = [];
        foreach ($tracks as $track) {
            $track_info = $music_brainz_info[$index];
            $tracks_info[$index] = [
                'number' => $index,
                'title' => $track_info['title'],
                'url' => realpath($track),
                'length' => FileUtils::getTrackLength($track),
                'artists' => $track_info['artists']
            ];

            $index++;
        }

        return $tracks_info;
    }

    private function createTracksInfoFromID3($tracks)
    {
        $tracks_info = [];
        $fail = 0;
        foreach ($tracks as $track) {
            if ($fail >= 3) { // If 4 or more tracks have no ID3, skips the process
                $tracks_info = [];
                break;
            }

            $id3 = new ID3($track);
            if (!$id3->hasTags()) { // If the tracks does not have id3 tags
                $fail++;
                continue;
            }

            $tracks_info[] = [
                'number' => $id3->getTrackNumber(),
                'title' => $id3->getTitle(),
                'url' => realpath($track),
                'length' => FileUtils::getTrackLength($track),
                'artists' => $id3->getLeadArtist()
            ];
        }

        return $tracks_info;
    }

    private function createTracksInfoFromFiles($tracks)
    {
        $tracks_info = [];
        $index = 1;
        foreach ($tracks as $track) {
            $tracks_info[] = [
                'number' => $index++,
                'title' => basename($track, ".mp3"),
                'url' => realpath($track),
                'length' => FileUtils::getTrackLength($track),
                'artists' => []
            ];
        }

        return $tracks_info;
    }
}