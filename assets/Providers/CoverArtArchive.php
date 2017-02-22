<?php

namespace Providers;

use InvalidArgumentException;

/**
 * Class CoverArtArchive retrieves a music album cover and thumbnails using its release ID.
 *
 * @author Stefano Frazzetto <s.frazzetto22@gmail.com>
 *
 * @version 1.0.0
 *
 * @see https://musicbrainz.org/doc/Cover_Art_Archive/API
 * @licence https://www.gnu.org/licenses/agpl-3.0.txt GNU AGPL v3
 */
class CoverArtArchive
{
    /**
     * @var string the album release ID
     */
    private $release_id;

    /**
     * @var array the raw json from CoverArtArchive
     */
    private $raw_array;

    /**
     * @var array the array containing the covers
     */
    private $covers;

    /**
     * @var array the array containing the small thumbnails
     */
    private $small_thumbnails;

    /**
     * @var array the array containing the large thumbnails
     */
    private $large_thumbnails;

    /**
     * CoverArtArchive constructor.
     *
     * @param string $release_id - the album release ID.
     */
    public function __construct($release_id)
    {
        if (empty($disc_id)) {
            throw new InvalidArgumentException('You must provide a release id.');
        }

        $this->release_id = $release_id;

        $json_images = @file_get_contents("http://coverartarchive.org/release/$release_id");
        $raw_array = json_decode($json_images);

        if ($json_images !== false && $raw_array !== null) {
            $this->raw_array = $raw_array;
            $images_array = $raw_array->images;
            foreach ($images_array as $image) {
                $this->covers[] = $image->image;
                $this->small_thumbnails[] = $image->thumbnails->small;
                $this->large_thumbnails[] = $image->thumbnails->large;
            }
        }
    }

    /**
     * Returns the raw array from CoverArtArchive.
     *
     * @return array - the raw array from CoverArtArchive
     */
    public function getRawJson()
    {
        return $this->raw_array;
    }

    /**
     * Returns only the array containing the covers for the chosen album.
     *
     * @return array - the array containing the covers for the chosen album.
     */
    public function getCovers()
    {
        return $this->covers;
    }

    /**
     * Returns the array containing the thumbnails (small) for the chosen album.
     *
     * @return array - the array containing the thumbnails (small) for the chosen album.
     */
    public function getSmallThumbnails()
    {
        return $this->small_thumbnails;
    }

    /**
     * the array containing the thumbnails (large) for the chosen album.
     *
     * @return array - the array containing the thumbnails (large) for the chosen album.
     */
    public function getLargeThumbnails()
    {
        return $this->large_thumbnails;
    }
}
