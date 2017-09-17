<?php

header('Content-Type: application/json');

require_once '../../vendor/autoload.php';

use Lib\FileUtils;
use Lib\MusicClasses\Album;

class ImportManager
{
    public static function importAllMissingAlbums()
    {
        $stats = [
            'total_albums' => Album::getAlbumsCount(),
            'scanned_albums' => count(static::findMissingAlbums()),
        ];

        foreach (self::findMissingAlbums() as $album) {
            Album::importJson(Album::getAlbumsRoot() . $album . '/', null, false, true);
        }

        $stats['created_albums'] = Album::getAlbumsCount() - $stats['total_albums'];

        return $stats;
    }

    public static function findMissingAlbums()
    {
        $all = self::findAlbumsDirectories();

        $loaded = Album::getAllAlbumsId();

        $diff = array_diff($all, $loaded);

        natsort($diff);

        return array_values($diff);
    }

    /**
     * @return array
     */
    public static function findAlbumsDirectories()
    {
        $dirs = FileUtils::getDirectories(Album::getAlbumsRoot());

        $dirs = array_diff($dirs, ['uploader']);

        $filtered_dirs = [];

        foreach ($dirs as $dir) {
            $path = Album::getAlbumsRoot() . $dir . '/' . Album::DATA_FILE;
            if (file_exists($path))
                $filtered_dirs[] = intval($dir);
        }

        return $filtered_dirs;
    }
}

echo json_encode(ImportManager::importAllMissingAlbums());