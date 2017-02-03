<?php

return [

    /**
     * |----------------------------------------------
     * | Application Name
     * |----------------------------------------------
     * | The name that will be shown in the page title.
     */
    'name' => "Jukebox",

    /**
     * |----------------------------------------------
     * | Application FQDN
     * |----------------------------------------------
     * | The FQDN of this jukebox.
     */
    'fqdn' => 'Jukebox',

    /**
     * |----------------------------------------------
     * | Application URL
     * |----------------------------------------------
     * | The URL used to access the application.
     */
    'url' => 'http://localhost',

    /**
     * |----------------------------------------------
     * | Application Debug Mode
     * |----------------------------------------------
     * | The application debug mode.
     */
    'debug' => true,

    /**
     * |----------------------------------------------
     * | Paths
     * |----------------------------------------------
     * | Paths to the most important directories.
     */
    'paths' => [
        'assets' => $_SERVER['DOCUMENT_ROOT'] . '/assets',
        'burner' => $_SERVER['DOCUMENT_ROOT'] . '/assets/php/burner/',
        'uploader' => $_SERVER['DOCUMENT_ROOT'] . '/jukebox/uploader/'
    ],

    /**
     * |----------------------------------------------
     * | Default database configuration
     * |----------------------------------------------
     * | Database configuration variables.
     */
    'database' => [
        'host' => "localhost",
        'name' => "jukebox",
        'user' => "root",
        'password' => "password1000",
    ],
];
