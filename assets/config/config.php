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
        'scripts' => [
            'path' => $_SERVER['DOCUMENT_ROOT'] . '/assets/cmd/',
            'disc' => $_SERVER['DOCUMENT_ROOT'] . '/assets/cmd/disc/'
        ],
        'uploader' => $_SERVER['DOCUMENT_ROOT'] . '/jukebox/uploader/'
    ],

    'disc' => [
        'scripts' => $_SERVER['DOCUMENT_ROOT'] . '/assets/cmd/disc/',
        'burner' => [
            'logs' => $_SERVER['DOCUMENT_ROOT'] . '/jukebox/disc/burner/logs/',
            'output' => $_SERVER['DOCUMENT_ROOT'] . '/jukebox/disc/burner/output/',
            'status' => $_SERVER['DOCUMENT_ROOT'] . '/jukebox/disc/burner/logs/'
        ],
        'ripper' => [
            'logs' => $_SERVER['DOCUMENT_ROOT'] . '/jukebox/disc/ripper/logs/',
            'output' => $_SERVER['DOCUMENT_ROOT'] . '/jukebox/disc/ripper/output/',
            'status' => $_SERVER['DOCUMENT_ROOT'] . '/jukebox/disc/ripper/logs/'
        ]
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
