<?php
/**
 * Created by PhpStorm.
 * User: vittorio
 * Date: 04/10/17
 * Time: 18:59
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Lib\Config;

$config = new Config();

echo json_encode($config->get('ports'));