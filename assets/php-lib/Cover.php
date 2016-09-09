<?php

require __DIR__ . '/jpgconverter.php';

class Cover
{
    const COVER_SIZE = 300;
    const THUMB_SIZE = 160;

    private $image; // A rsource type containing the image

    function __construct($url)
    {
        if (isset($url))
            return $this->loadImagefromUrl($url);
        return null;
    }

    function loadImagefromUrl($url)
    {
        // TODO VALIDATE URL

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

    function getResampledInstance($sizex, $sizey)
    {
        $width = imagesx($this->image);
        $height = imagesy($this->image);

        //$x = $sizex;
        $y = $sizey;


        if($height < $sizey){
            $y = $height;
        }

//        if($width < $sizex){
//            $x = $width ;
//        }

        //we should't let the picture be magnified

        $x = intval($width / $height * $y); //Used to maintain the ratio

        $tmp = imagecreatetruecolor($x, $y);

        imagecopyresampled($tmp, $this->image, 0, 0, 0, 0, $x, $y, $width, $height);

        return $tmp;
    }

    private function getCoverImage(){
        return $this->getResampledInstance(self::COVER_SIZE, self::COVER_SIZE);
    }

    private function getThumbImage(){
        return $this->getResampledInstance(self::THUMB_SIZE, self::THUMB_SIZE);
    }

    function saveToAlbum($id)
    {
        $cover = $this->getCoverImage();
        $thumb = $this->getThumbImage();

        $path = $_SERVER['DOCUMENT_ROOT'] . "/jukebox/$id";

        if(!file_exists ($path)){
            throw new Exception('Album folder does not exist.');
        }

        $this->saveImageToFile($cover, "$path/cover.jpg");
        $this->saveImageToFile($thumb, "$path/thumb.jpg");
    }

    function saveImageToFile($image, $path){
        imagejpeg($image, $path, 100);
    }

    function saveToFile($path)
    {
        $this->saveImageToFile($this->image, $path);
    }

    function saveToRadio($radio_id)
    {
        // TODO IMPLEMENT THISSS1 :D
    }
}