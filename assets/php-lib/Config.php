<?php

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
 * @version 1.1.0
 */
class Config
{
    /** The directory containing the configuration files */
    const CONFIG_DIR = "../config/";

    /** The json configuration file */
    private $dynamic_conf_file;

    /** The static configuration */
    private $static_conf;

    /** The dynamic configuration */
    private $dynamic_config;

    public function __construct()
    {
        // Loads the static configuration file
        $this->static_conf = include(self::CONFIG_DIR . "config.php");

        // Loads the dynamic configuration file
        $this->dynamic_conf_file = self::CONFIG_DIR . "config.json";
        if (!file_exists($this->dynamic_conf_file)) {
            file_put_contents($this->dynamic_conf_file, null);
        }

        $this->dynamic_config = json_decode(file_get_contents($this->dynamic_conf_file), true);
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
     * @return mixed - the value associated with the config key
     */
    public function get($key, $static = false)
    {
        if (isset($this->dynamic_config[$key]) && !$static) {
            return $this->getDynamic($key);
        } elseif (isset($this->static_conf[$key])) {
            return $this->getStatic($key);
        } else {
            return null;
        }
    }

    /**
     * Returns the value associated with the key from the dynamic configuration file.
     *
     * @param $key - the key to search
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
     * @return mixed - the return value(s)
     */
    private function getStatic($key)
    {
        return $this->static_conf[$key];
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
     * Saves the configuration into the dynamic configuration file in json format.
     */
    private function saveDynamicConfig()
    {
        file_put_contents($this->dynamic_conf_file, json_encode($this->dynamic_config));
    }

}