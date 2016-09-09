<?php

// SE MAGARI LO AVESSI COMMENTATO SAREBBE STATO MEGLIO MANNAGGIA AL DIOFONO

function convertImage($originalImage, $outputImage, $quality, $MaxHeight, $MaxWidth) {
    if (($img_info = getimagesize($originalImage)) === FALSE)
        return false;

    switch ($img_info[2]) {
        case IMAGETYPE_GIF : $src = imagecreatefromgif($originalImage);
            break;
        case IMAGETYPE_JPEG : $src = imagecreatefromjpeg($originalImage);
            break;
        case IMAGETYPE_PNG : $src = imagecreatefrompng($originalImage);
            break;
        case IMAGETYPE_BMP : $src = imagecreatefrombmp($originalImage);
            break;
        default : return false;
    }

    $width = imagesx($src);
    $height = imagesy($src);

    $y = $MaxHeight;
    $x = $MaxWidth;

    if($height < $y){
        $y = $height;
    }
    if($width < $x){
        $x = $width ;
    }

    //we should't let the picture be magnified

    $x = intval($width / $height * $y); //Used to maintain the ratio

    $tmp = imagecreatetruecolor($x, $y);

    imagecopyresampled($tmp, $src, 0, 0, 0, 0, $x, $y, $width, $height);

    imagejpeg($tmp, $outputImage, $quality);

    return true;
}
