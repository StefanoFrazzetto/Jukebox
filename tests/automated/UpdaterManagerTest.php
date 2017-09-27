<?php


require_once __DIR__.'/../JukeboxTestClass.php';

final class UpdaterManagerTest extends JukeboxTestClass
{
    /**
     * @var string the directory where test files can be created
     */
    const TEST_DIR = '/tmp/tests/';

    /**
     * @var string the test file(s) name
     */
    const TEST_FILE_NAME = 'updatertest.txt';

    public function testRun1()
    {
        $updaterManager = new \Lib\UpdaterManager(true);

        $this->assertTrue($updaterManager->run());
    }

    public function testFileCreated()
    {
        $this->assertTrue(file_exists(static::TEST_DIR.static::TEST_FILE_NAME));
    }

    /**
     * Create the tests directory.
     */
    public static function setUpBeforeClass()
    {
        mkdir(self::TEST_DIR, 0777, true);

        $updates_path = \Lib\Config::getPath('updater');
        if (!\Lib\FileUtils::emptyDirectory($updates_path)) {
            throw new Exception('Cannot empty the updates directory');
        }

        $datetime = date('YmdHis');
        $testfile_path = static::TEST_DIR.static::TEST_FILE_NAME;
        $fake_update = [
            'author'   => 'Stefano Frazzetto',
            'date'     => $datetime,
            'aptitude' => [
                'autoremove' => ['nano'],
                'install'    => ['nano'],
            ],
            'raw' => ["echo '[$datetime] Running automated updater test' > $testfile_path"],
        ];

        $updateFile = $updates_path.$datetime.'_test_update.json';
        $write = file_put_contents($updateFile, json_encode($fake_update));
        if ($write === false) {
            throw new Exception('Cannot write the update file');
        }
    }

    /**
     * Remove the tests directory and all the files/folder it contains.
     */
    public static function tearDownAfterClass()
    {
        $updates_path = \Lib\Config::getPath('updater');
        if (!\Lib\FileUtils::emptyDirectory($updates_path)) {
            throw new Exception('Cannot empty the updates directory');
        }

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
