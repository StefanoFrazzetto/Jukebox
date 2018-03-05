<?php

require_once __DIR__.'/../JukeboxTestClass.php';

final class ZipperTest extends JukeboxTestClass
{
    /**
     * @var string the directory where test files can be created
     */
    const TEST_DIR = '/tmp/tests/zipper/';

    /**
     * @var string the test file(s) name
     */
    const TEST_FILE_NAME = 'fakefile';

    /**
     * @var string the extension for the test file(s)
     */
    const TEST_FILE_EXTENSION = '.mp3';

    /**
     * @var int the number of test files to create
     */
    const TEST_FILES_NUMBER = 20;

    /**
     * Create the tests directory.
     */
    public static function setUpBeforeClass()
    {
        if (!file_exists(self::TEST_DIR)) {
            mkdir(self::TEST_DIR, 0777, true);
        }

        for ($i = 0; $i < self::TEST_FILES_NUMBER; $i++) {
            $file_name = self::TEST_DIR.self::TEST_FILE_NAME.$i.self::TEST_FILE_EXTENSION;
            file_put_contents($file_name, "random_$i");
        }
    }

    public function testFake()
    {
        $this->assertTrue(true);
    }

    /**
     * Remove the tests directory and all the files/folder it contains.
     */
    public static function tearDownAfterClass()
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::TEST_DIR, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }

        rmdir(self::TEST_DIR);
    }
}
