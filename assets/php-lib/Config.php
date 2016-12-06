<?php

/**
 * Class Config provides access to the configuration file(s). When retrieving
 *
 * @author Stefano Frazzetto - https://github.com/StefanoFrazzetto
 * @version 1.0.0
 */
class Config
{
    /** The directory containing the configuration files */
    const CONFIG_DIR = "../config/";

    /** The PHP configuration file */
    private $php_config;

    /** The json configuration file */
    private $json_file;

    /** The json configuration */
    private $json_config;

    public function __construct()
    {
        // Loads the configuration file
        $this->php_config = include(self::CONFIG_DIR . "config.php");
        $this->loadJsonConfig();
    }

    private function loadJsonConfig()
    {
        $this->json_file = self::CONFIG_DIR . "config.json";
        if (!file_exists($this->json_file)) {
            file_put_contents($this->json_file, null);
        }

        $this->json_config = json_decode(file_get_contents($this->json_file), true);
    }

    /**
     * Returns the config key passed as parameter if set, otherwise null.
     *
     * @param $key - the key to return
     * @return mixed - the value associated with the config key
     */
    public function get($key)
    {
        if (isset($this->php_config[$key]) && !isset($source)) {
            return $this->php_config[$key];
        } elseif ($source = "json") {
            return $this->getJsonConfig($key);
        } else {
            return null;
        }
    }

    /**
     * Retuns the value associated with the key from the json configuration.
     *
     * @param $key - the key to search
     * @return mixed - the return value(s)
     */
    private function getJsonConfig($key)
    {
        return $this->json_config[$key];
    }

    /**
     * Stores the value of a key in the json configuration file.
     *
     * @param $key - the key
     * @param $value - the value associated to the key
     */
    public function set($key, $value)
    {
        $this->json_config[$key] = $value;
        $this->saveJsonConfig();
    }

    /**
     * Saves the configuration into the json file.
     */
    private function saveJsonConfig()
    {
        file_put_contents($this->json_file, json_encode($this->json_config));

    }

}