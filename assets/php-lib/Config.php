<?php

namespace Lib;

use InvalidArgumentException;

/**
 * Class Config provides access to the configuration of this software.
 *
 * It uses both static and dynamic configuration for different purposes:
 *
 * - The static configuration makes use of PHP file which can only be read and holds the configuration
 *   variables that make the software run properly.
 *
 * - The dynamic configuration makes use of a json file which can be read and edited in order to allow
 *   runtime changes to the system configuration.
 *
 * @author Stefano Frazzetto - https://github.com/StefanoFrazzetto
 *
 * @version 1.1.0
 */
class Config
{
    /** The json configuration file */
    private $dynamic_conf_file;

    /** The static configuration */
    private $static_conf;

    /** The dynamic configuration */
    private $dynamic_config;

    public function __construct()
    {
        $config_dir = __DIR__.'/../config/';

        // Loads the static configuration file
        $this->static_conf = include $config_dir.'config.php';

        // Loads the dynamic configuration file
        $this->dynamic_conf_file = $config_dir.'config.json';
        if (!file_exists($this->dynamic_conf_file)) {
            file_put_contents($this->dynamic_conf_file, null);
        }

        $this->dynamic_config = json_decode(file_get_contents($this->dynamic_conf_file), true);
    }

    /**
     * Return one or more paths from the configuration file.
     *
     * If the first parameter is not specified, an array of paths is returned.
     *
     * @param string $path The path to get from the config file.
     *
     * @return mixed|null If a path is passed, the specified path is returned
     *                    if it does exist, otherwise null is returned. If no parameter is passed,
     *                    the array of paths is returned instead.
     */
    public static function getPath($path = '')
    {
        $config = new self();
        $paths = $config->get('paths');

        if (empty($paths)) {
            return $paths;
        }

        return isset($paths[$path]) ? $paths[$path] : null;
    }

    /**
     * Set the ports number and restart their respective services.
     *
     * @param array $modal the associative array containing the port
     *                     name and value.
     *
     * @return bool
     */
    public function setPorts($modal)
    {
        if (!is_array($modal)) {
            throw new InvalidArgumentException('The argument is not an modal');
        }

        // Check for duplicates
        if (count($modal) !== count(array_unique($modal))) {
            throw new InvalidArgumentException('Duplicate values are not allowed');
        }

        $success = true;
        $system = new System();
        $ports = $this->get('ports');
        $reserved_ports = System::RESERVED_PORTS;

        // HTTP
        if (isset($modal['http'])) {
            $http = $modal['http'];

            if (!in_array($http, $reserved_ports) || $http > 1024) {
                if ($http !== System::getHttpPort()) {
                    $success &= $system->setHttpPort($http);
                    $this->setPortConfig('http', $http);
                }
            }
        }

        // SSH
        if (isset($modal['ssh'])) {
            $ssh = $modal['ssh'];

            if (!in_array($ssh, $reserved_ports) || $ssh === 22) {
                if ($ssh !== System::getSshPort()) {
                    $success &= $system->setSshPort($ssh);
                    $this->setPortConfig('ssh', $ssh);
                }
            }
        }

        // Remote & Radio
        if (isset($modal['remote']) || isset($modal['radio'])) {
            $remote = $modal['remote'];
            $radio = $modal['radio'];

            if (!in_array($remote, $reserved_ports) || $remote > 1024) {
                if ($ports['remote'] !== $remote) {
                    $this->setPortConfig('remote', $remote);
                }
            }

            if (!in_array($radio, $reserved_ports) || $radio > 1024) {
                if ($ports['radio'] !== $radio) {
                    $this->setPortConfig('radio', $radio);
                }
            }

            // Restart the service if either one was changed
            $success &= $system->reloadNodeServerService();
        }

        // Save EVERYTHING!
        $this->retrieveDefaultPorts();
        $this->saveDynamicConfig();

        return $success;
    }

    /**
     * Returns the config key passed as parameter. If exists, the dynamic value will be returned,
     * otherwise the static configuration value will be returned. In case the value is not found
     * in any configuration file, null will be returned.
     * If you want to force the static config to be returned, set the static flag to true.
     *
     *
     * @param $key - the key to return
     * @param $static - flag to force the static config to be returned
     *
     * @return mixed - the value associated with the config key
     */
    public function get($key, $static = false)
    {
        if (isset($this->dynamic_config[$key]) && !$static) {
            return $this->getDynamic($key);
        } elseif (isset($this->static_conf[$key])) {
            return $this->getStatic($key);
        } else {
            return;
        }
    }

    /**
     * Set the dynamic ports config.
     *
     * @param $key
     * @param $value
     */
    private function setPortConfig($key, $value)
    {
        $this->dynamic_config['ports'][$key] = intval($value);
    }

    /**
     * Saves the configuration into the dynamic configuration file in json format.
     */
    private function saveDynamicConfig()
    {
        file_put_contents($this->dynamic_conf_file, json_encode($this->dynamic_config, JSON_PRETTY_PRINT));
    }

    /**
     * Stores the value of a key in the json configuration file.
     *
     * @param $key - the key
     * @param $value - the value associated to the key
     */
    public function set($key, $value)
    {
        $this->dynamic_config[$key] = $value;
        $this->saveDynamicConfig();
    }

    /**
     * If some (or all ports) were left with their default value,
     * save these values into the dynamic configuration file. Otherwise the
     * dynamic configuration for the ports could have contained NULL values.
     */
    private function retrieveDefaultPorts()
    {
        $dynamic_ports = isset($this->dynamic_config['ports']) ? $this->getDynamic('ports') : [];
        $static_ports = $this->getStatic('ports');

        $diff = array_diff(array_keys($static_ports), array_keys($dynamic_ports));
        $dynamic_ports_array = &$this->dynamic_config['ports'];

        foreach ($diff as $port_key) {
            $dynamic_ports_array[$port_key] = $static_ports[$port_key];
        }
    }

    /**
     * Returns the value associated with the key from the dynamic configuration file.
     *
     * @param $key - the key to search
     *
     * @return mixed - the return value(s)
     */
    private function getDynamic($key)
    {
        return $this->dynamic_config[$key];
    }

    /**
     * Returns the value associated with the key from the static configuration file.
     *
     * @param $key - the key to search
     *
     * @return mixed - the return value(s)
     */
    private function getStatic($key)
    {
        return $this->static_conf[$key];
    }
}
