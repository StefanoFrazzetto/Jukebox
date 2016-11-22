<?php

class Zipper {

    public function zip($albumMap, $outputFileName, $folderToZip = '') {

        if ($folderToZip == '') {
            die('Error: folder not selected.');
        }

        $sourceFolderToZip = '../../jukebox/' . $folderToZip;
        $outputFile = '../../downloads/' . $outputFileName . '.zip';

        //Create the ZipArchive object
        $zip = new ZipArchive;

        if ($zip->open($outputFile, ZipArchive::CREATE) !== TRUE) {
            die("Could not create archive.");
        }

        foreach ($albumMap as $key => $value) {

            for ($i = 0; $i < count($value); $i++) {
                // Close the zip file and re-open it if the number of files is equal to the limit.
                if ($i == 50) {
                    $zip->close();
                    $zip->open($outputFile) or die("Error: Could not reopen Zip");
                }

                if (count($albumMap) != 1) {
                    $localPath = $outputFileName . '/' . 'CD' . $key . '/' . $value[$i];
                    $fullPath = $sourceFolderToZip . '/' . $value[$i];
                } else {
                    $localPath = $outputFileName . '/' . $value[$i];
                    $fullPath = $sourceFolderToZip . '/' . $value[$i];
                }
                
                //Check if the file exists before adding it to the ZIP file, otherwise the process will fail.
                if (file_exists($fullPath)) {
                    $zip->addFromString($localPath, $fullPath) or die("ERROR: Could not add file: $key </br> numFile:" . $zip->numFiles);
                    $zip->addFile(realpath($fullPath), $localPath) or die("ERROR: Could not add file: $key </br> numFile:" . $zip->numFiles);
                }// end if
            }
        } // end foreach

        $coverLocalPath = $outputFileName . '/' . 'cover.jpg';
        $coverFullPath = $sourceFolderToZip . '/' . 'cover.jpg';

        //Check if the file exists before adding it to the ZIP file, otherwise the process will fail.
        if (file_exists($coverFullPath)) {
            $zip->addFromString($coverLocalPath, $coverFullPath) or die("ERROR: Could not add file: $key </br> numFile:" . $zip->numFiles);
            $zip->addFile(realpath($coverFullPath), $coverLocalPath) or die("ERROR: Could not add file: $key </br> numFile:" . $zip->numFiles);
        }

        $zip->close();
    }

}
