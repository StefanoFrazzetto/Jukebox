<?php

require_once __DIR__.'/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

class JukeboxTestClass extends TestCase
{
    public static function setUpBeforeClass()
    {
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/html';
    }
}
