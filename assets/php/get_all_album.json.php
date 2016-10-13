<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 13-Oct-16
 * Time: 14:34
 */

require '../php/Database.php';

header('Content-Type: application/json');

$database = new Database();

$albums = $database->select('id, title, artist');

echo json_encode($albums);