<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 05-Nov-16
 * Time: 11:11
 */

require '../php-lib/Radio.php';

header('Content-Type: application/json');

$database = new Database();

$radios = Radio::getAllRadios();

echo json_encode($radios);