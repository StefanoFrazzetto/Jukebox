<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

class JukeboxTestClass extends TestCase
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
        mkdir(self::TEST_DIR, 0777);
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