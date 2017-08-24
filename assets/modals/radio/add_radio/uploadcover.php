<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 11-Oct-16
 * Time: 17:22.
 */
require_once '../../../../vendor/autoload.php';

use Lib\FileUtils;

header('Content-Type: application/json');

$url = $_GET['url'];

$covers_path = '/jukebox/radio-covers/';

$absolute_path = '/var/www/html'.$covers_path;

try {
    if (file_exists($absolute_path))
        FileUtils::deleteFilesOlderThan($absolute_path, 86400);

    $file = file_get_contents($url);
    $ext = $ext = pathinfo($url, PATHINFO_EXTENSION);

    if ($file === false) {
        echo json_encode(['status' => 'error', 'message' => 'File not found']);
        exit;
    }

    if (!is_dir($absolute_path)) {
        mkdir($absolute_path);
    }

    $filename = uniqid('temp', true).".$ext";

    file_put_contents($absolute_path.$filename, $file);

    echo json_encode(['status' => 'success', 'url' => $covers_path.$filename]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
