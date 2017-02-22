<?php

require_once __DIR__.'/JukeboxTestClass.php';
use Lib\FileUtils;

final class FileUtilsTest extends JukeboxTestClass
{
    public function testCreateFile()
    {
        $this->assertTrue(FileUtils::createFile(self::TEST_FILE), 'Cannot create file.');
    }

    /**
     * @depends testCreateFile
     */
    public function testCountFiles()
    {
        $this->assertEquals(1, FileUtils::countFiles(self::TEST_DIR), 'Cannot count files.');
    }

    /**
     * @depends testCountFiles
     */
    public function testRemove()
    {
        $this->assertTrue(FileUtils::remove(self::TEST_FILE), 'Cannot remove files.');
    }

    /**
     * @depends testRemove
     */
    public function testIsDirEmpty()
    {
        $this->assertTrue(FileUtils::isDirEmpty(self::TEST_DIR), 'Cannot test if dir is empty.');
    }
}
