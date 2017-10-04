<?php

namespace Lib;

use InvalidArgumentException;

/**
 * Class System handles the jukebox soft and hard reset.
 *
 * This class also provides methods to fix permission settings,
 * apache and ssh settings, etc.
 *
 * @author Stefano Frazzetto
 *
 * @version 2.0.0
 */
class System
{
    /** @var string */
    const HTTP_CONFIG_FILE = '/etc/apache2/ports.conf';
    /** @var string */
    const SSH_CONFIG_FILE = '/etc/ssh/sshd_config';
    /** @var Database $database the database */
    private $database;
    /** @var string the absolute path to the albums storage */
    private $albums_storage_path;
    /** @var string the absolute path to the web-server directory */
    private $document_root;

    const RESERVED_PORTS = [0, 22, 80, 443, 3306, 4201];

    public function __construct()
    {
        $this->database = new Database();

        $config = new Config();
        $this->albums_storage_path = $config->get('paths')['albums_root'];
        $this->document_root = $config->get('paths')['document_root'];
    }

    /**
     * // TODO.
     */
    public static function getSinkInputs()
    {
        $command = 'pacmd list-sink-inputs';
        $regex = '/index: ([0-9]+)/';
        $res = OS::execute($command);

        preg_match_all($regex, $res, $matches);

        var_export($res);
    }

    /**
     * @param int $decimals [optional] Sets the number of decimal points.
     *
     * @return string
     */
    public static function getSoctemp($decimals = 1)
    {
        $temp = trim(OS::execute('cat /etc/armbianmonitor/datasources/soctemp'));
        $temp = floatval($temp) / 1000;
        $temp = number_format($temp, $decimals, '.', '');

        return $temp;
    }

    /**
     * @return string
     */
    public static function getPmictemp()
    {
        $temp = trim(OS::execute('cat /etc/armbianmonitor/datasources/pmictemp'));
        $temp = floatval($temp) / 1000;
        $temp = number_format($temp, 2, '.', '');

        return $temp;
    }

    /**
     * Update the system packages.
     *
     * @return string
     */
    public static function upgrade()
    {
        return OS::executeWithResult('sudo aptitude upgrade -y');
    }

    /**
     * Update the system dependencies.
     *
     * @return string
     */
    public static function update()
    {
        return OS::executeWithResult('sudo aptitude update');
    }

    /**
     * Shutdown the system.
     */
    public static function shutdown()
    {
        OS::execute('sudo poweroff');
    }

    /**
     * Reboot the system.
     */
    public static function reboot()
    {
        OS::execute('sudo reboot');
    }

    /**
     * Eject the disc.
     */
    public static function eject()
    {
        $device = OS::execute("lsblk | grep rom | cut -d' ' -f1");
        OS::execute("eject $device");
    }

    /**
     * @param $port
     *
     * @return bool
     */
    public function setHttpPort($port)
    {
        if (!is_numeric($port)) {
            throw new InvalidArgumentException('Invalid port argument');
        }

        $res_set = OS::executeWithResult("sudo sed -i -e 's/Listen ".self::getHttpPort()."/Listen $port/' ".self::HTTP_CONFIG_FILE);

        return  $res_set && self::reloadApacheService();
    }

    /**
     * @return int
     */
    public static function getSshPort()
    {
        $value = OS::execute("grep 'Port' ".self::SSH_CONFIG_FILE." | awk {'print $2'}");

        return intval($value);
    }

    /**
     * @param $port
     *
     * @return bool
     */
    public function setSshPort($port)
    {
        if (!is_numeric($port)) {
            throw new InvalidArgumentException('Invalid port argument');
        }

        $res_set = OS::executeWithResult("sudo sed -i -e 's/Port ".self::getSshPort()."/Port $port/' ".self::SSH_CONFIG_FILE);

        return  $res_set && self::reloadSshService();
    }

    /**
     * @return int
     */
    public static function getHttpPort()
    {
        $value = OS::execute("grep 'Listen' ".self::HTTP_CONFIG_FILE." | grep -v 'Listen 443' | awk {'print $2'}");

        return intval($value);
    }

    /**
     * @return bool
     */
    public function reloadApacheService()
    {
        return OS::executeWithResult('sudo /etc/init.d/apache2 reload');
    }

    /**
     * @return bool
     */
    public function reloadNodeServerService()
    {
        return OS::executeWithResult('sudo /etc/init.d/nodeserver force-reload');
    }

    /**
     * @return bool
     */
    public function reloadSshService()
    {
        return OS::executeWithResult('sudo /etc/init.d/ssh reload');
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

    /**
     * // TODO.
     */
    private function getSinks()
    {
        $command = 'pacmd list-sinks';
    }
}
