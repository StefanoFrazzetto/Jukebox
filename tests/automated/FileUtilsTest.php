<?php

require_once __DIR__.'/../JukeboxTestClass.php';

use Lib\FileUtils;

final class FileUtilsTest extends JukeboxTestClass
{
    /**
     * @var string the directory where test files can be created.
     */
    const TEST_DIR = '/tmp/tests/';

    /**
     * @var string the path to a file that can be used for tests.
     */
    const TEST_FILE = '/tmp/tests/test.txt';

    /**
     * Create the tests directory.
     */
    public static function setUpBeforeClass()
    {
        if (!file_exists(self::TEST_DIR)) {
            mkdir(self::TEST_DIR, 0777);
        }
    }

    /**
     * Remove the tests directory and all the files/folder it contains.
     */
    public static function tearDownAfterClass()
    {
        FileUtils::remove(static::TEST_DIR, true);
    }

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
        FileUtils::emptyDirectory(self::TEST_DIR);
        $this->assertTrue(FileUtils::isDirEmpty(self::TEST_DIR), 'Cannot test if dir is empty.');
    }
}
