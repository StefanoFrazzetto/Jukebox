<?php


use Lib\FileUtils;
use Lib\UpdaterManager;

require_once __DIR__.'/../JukeboxTestClass.php';

final class UpdaterManagerTest extends JukeboxTestClass
{
    /**
     * @var string the directory where test files can be created
     */
    const TEST_DIR = '/tmp/tests/updater/';

    /**
     * @var string the test file(s) name
     */
    const TEST_FILE_NAME = 'updatertest.txt';

    public function testRun1()
    {
        $updaterManager = new UpdaterManager();

        $this->assertTrue($updaterManager->runSystemUpdates());
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
        // Create the test dir
        if (!file_exists(static::TEST_DIR)) {
            mkdir(static::TEST_DIR, 0777, true);
        }

        $datetime = date('YmdHis');
        $testfile_path = static::TEST_DIR.static::TEST_FILE_NAME;
        $fake_update = [
            'author'   => 'Stefano Frazzetto',
            'date'     => $datetime,
            'aptitude' => [
                'install'    => ['nano'],
            ],
            'raw' => ["echo '[$datetime] Running automated updater test' > $testfile_path"],
        ];

        $updateFile = UpdaterManager::getUpdatesDirectory().$datetime.'_test_update.json';
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
        FileUtils::remove(static::TEST_DIR, true);

        $git = new \Lib\Git();
        $git->pull(null, true);
    }
}
