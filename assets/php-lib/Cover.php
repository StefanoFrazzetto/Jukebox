<?php

namespace Lib;

use Exception;

class Cover
{
    const COVER_SIZE = 300;
    const THUMB_SIZE = 162;

    /** @const array The array of allowed covers extensions */
    const ALLOWED_COVER_EXTENSIONS = ['jpg', 'png', 'jpeg', 'gif'];

    private $image; // A resource type containing the image

    public function __construct($url)
    {
        if (isset($url) && !empty($url)) {
            return $this->loadImageFromUrl($url);
        } else {
            throw new Exception('No url provided.');
        }
    }

    public function loadImageFromUrl($url)
    {
        if (!@getimagesize($url)) {
            throw new Exception("Failed to load image because '$url' is not available.");
        }

        $type = exif_imagetype($url);

        switch ($type) {
            case 1:   //   gif -> jpg
                $this->image = imagecreatefromgif($url);
                break;
            case 2:   //   jpeg -> jpg
                $this->image = imagecreatefromjpeg($url);
                break;
            case 3:  //   png -> jpg
                $this->image = imagecreatefrompng($url);
                break;
        }

        return true;
    }

    public function saveToAlbum($id)
    {
        $path = $_SERVER['DOCUMENT_ROOT']."/jukebox/$id";

        $this->saveAlbumImagesToFolder($path);
    }

    public function saveAlbumImagesToFolder($path)
    {
        $cover = $this->getCoverImage();
        $thumb = $this->getThumbImage();

        if (!file_exists($path)) {
            throw new Exception('Album folder does not exist.');
        }

        $this->saveImageToFile($cover, "$path/cover.jpg");
        $this->saveImageToFile($thumb, "$path/thumb.jpg");
    }

    private function getCoverImage()
    {
        return $this->getResampledInstance(self::COVER_SIZE, self::COVER_SIZE);
    }

    public function getResampledInstance($sizex, $sizey, $mantain_ration = false)
    {
        $width = imagesx($this->image);
        $height = imagesy($this->image);

        if ($mantain_ration) {

            //$x = $sizex;
            $y = $sizey;

            if ($height < $sizey) {
                $y = $height;
            }

            //we should't let the picture be magnified

            $x = intval($width / $height * $y); //Used to maintain the ratio

            $tmp = imagecreatetruecolor($x, $y);

            imagecopyresampled($tmp, $this->image, 0, 0, 0, 0, $x, $y, $width, $height);
        } else {
            $tmp = imagecreatetruecolor($sizex, $sizey);

            imagecopyresampled($tmp, $this->image, 0, 0, 0, 0, $sizex, $sizey, $width, $height);
        }

        return $tmp;
    }

    private function getThumbImage()
    {
        return $this->getResampledInstance(self::THUMB_SIZE, self::THUMB_SIZE);
    }

    public function saveImageToFile($image, $path)
    {
        imagejpeg($image, $path, 100);
    }

    public function saveToFile($path)
    {
        $this->saveImageToFile($this->image, $path);
    }

    public function saveToRadio($radio_id)
    {
        // TODO IMPLEMENT THISSS1 :D
    }
}
