<?php

require_once __DIR__ . '/JukeboxTest.php';
use Lib\FileUtils;

final class FileUtilsTest extends JukeboxTest
{
    public function testCreateFile()
    {
        $this->assertTrue(FileUtils::createFile("test.txt"), 'Cannot create file.');
    }
}