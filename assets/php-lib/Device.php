<?php

namespace Lib;

/**
 * Class Device handles the jukebox soft and hard reset.
 *
 * This class also provides methods to fix permission settings,
 * apache and ssh settings, etc.
 *
 * @author Stefano Frazzetto
 *
 * @version 1.0.0
 */
class Device
{
    /** @var Database $database the database */
    private $database;

    /** @var string the absolute path to the albums storage */
    private $albums_storage_path;

    /** @var string the absolute path to the web-server directory */
    private $document_root;

    public function __construct()
    {
        $this->database = new Database();

        $config = new Config();
        $this->albums_storage_path = $config->get('paths')['albums_root'];
        $this->document_root = $config->get('paths')['document_root'];
    }

    public static function shutdown()
    {
        OS::execute('sudo poweroff');
    }

    public static function reboot()
    {
        OS::execute('sudo reboot');
    }

    public static function eject()
    {
        $device = OS::execute("lsblk | grep rom | cut -d' ' -f1");
        OS::execute("eject $device");
    }

    /**
     * Fix apache config.
     *
     * Copy the custom .ini configuration for apache and set its permissions
     * to 777 as the other files in the target directory.
     *
     * @return bool true if the custom ini file was copied, false otherwise.
     */
    private function fixApacheConfig()
    {
        $PHP_INI_TARGET = '/etc/php5/apache2/conf.d/.user.ini';
        $PHP_INI_SOURCE = $this->document_root.'/installation/.user.ini';

        $res_copy = OS::executeWithResult("sudo cp -f $PHP_INI_SOURCE $PHP_INI_TARGET");
        $res_perm = OS::executeWithResult("sudo chmod 777 $PHP_INI_TARGET");

        return $res_copy && $res_perm;
    }

    /**
     * Fix files and directories permissions.
     *
     * Set directory permissions to 775 and file permissions to 664 recursively.
     */
    private function fixPermissions()
    {
        $user = OS::execute('whoami');

        $res_own = OS::executeWithResult("sudo chown -R $user:$user $this->document_root");
        $res_perm = OS::executeWithResult("sudo find $this->document_root -type f -exec chmod 744 {} + -o -type d -exec chmod 775 {} +");

        return $res_own && $res_perm;
    }

    /**
     * Reset the jukebox to factory settings.
     *
     * @return bool
     */
    public function hardReset()
    {
        $res_perm = $this->fixPermissions();
        $res_apache = $this->fixApacheConfig();

        $database = new Database();
        $database->resetDatabase();
        $res_erase = FileUtils::emptyDirectory($this->albums_storage_path);

        return $res_perm && $res_apache && $res_erase;
    }

    /**
     * Delete all songs and radio stations.
     *
     * @return bool true on success, false otherwise.
     */
    public function softReset()
    {
        $database_res = $this->database->truncate('all');
        $storage_res = FileUtils::emptyDirectory($this->albums_storage_path);

        return $database_res && $storage_res;
    }
}
