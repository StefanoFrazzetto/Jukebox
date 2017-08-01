<?php

namespace Lib;

use DOMDocument;
use DOMXPath;

/**
 * Class ImageFetcher retrieves albums images, covers, thumbnails from multiple source.
 * Non-working images are automatically removed.
 *
 * @author Stefano Frazzetto - https://github.com/StefanoFrazzetto
 *
 * @version 1.0.0
 * @licence GNU AGPL v3 - https://www.gnu.org/licences/agpl-3.0.txt
 */
class ImageFetcher
{
    private $artist;
    private $album;
    private $_search_query;

    /**
     * Constructor.
     * Gets the artist and album, and creates the search query.
     *
     * @param string $artist - the artist
     * @param string $album  - the album
     */
    public function __construct($artist, $album)
    {
        $this->artist = str_replace(' ', '+', $artist);
        $this->album = str_replace(' ', '+', $album);
        $this->_search_query = $this->artist.'+'.$this->album;
    }

    public function getAll()
    {
        $images = [];
        $images_array = [];

        if (Network::isConnected()) {
            $images['covershut'] = $this->getFrom("http://www.covershut.com/cover-tags.html?covertags=$this->_search_query&search=Search", 15);
            $images['allmusic'] = $this->getFrom("http://www.allmusic.com/search/albums/$this->_search_query", 4);
            $images['slothradio'] = $this->getFrom("http://covers.slothradio.com/?adv=&artist=$this->artist&album=$$this->album", 2);
//        $images[] = $this->getFrom("http://www.seekacover.com/cd/$this->_search_query", 2);
//        $images[] = $this->getFrom("https://www.google.co.uk/search?q=$this->_search_query&tbm=isch&tbs=isz:l", 10);
//        $images["discogs"] = $this->getFrom("https://www.discogs.com/search/?q=$this->_search_query&type=all", 10);
//        $images[] = $this->getYoutube($this->_search_query);
        }

        foreach ($images as $website) {
            $images_array = array_merge($images_array, $website);
        }

        $this->removeUselessImages($images_array);

        return $images_array;
    }

    private function getFrom($url, $no = 2)
    {
        $html = file_get_contents($url);

        // Return false on failure
        if (!$html) {
            return;
        }

        $dom = new DOMDocument();
        @$dom->loadHTML($html);

        $xpath = new DOMXPath($dom);
        $imgs = $xpath->evaluate('/html/body//img');

        $imageurls = [];

        for ($i = 0; $i < $no; $i++) {
            if (!$imgs->item($i) == null) {
                $img = $imgs->item($i);
                /** @noinspection PhpUndefinedMethodInspection */
                $url = $img->getAttribute('src');
                $imageurls[$i] = $url;
            }
        }

        array_splice($imageurls, 0, 0);

        if (!empty($imageurls)) {
            return $imageurls;
        } else {
            return;
        }
    }

    /**
     * Removes all the duplicates and the data:image from the images array.
     *
     * @param $images_array - the array containing the images URLs.
     */
    private function removeUselessImages(&$images_array)
    {
        foreach ($images_array as $key => $url) {
            if (preg_match('(data:image|paypal)', $url) === 1 || preg_match('(png|jpg)', $url) == 0) {
                unset($images_array[$key]);
            }
        }

        $images_array = array_values(array_unique($images_array));
    }
}
