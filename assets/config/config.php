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
        'scripts' => $_SERVER['DOCUMENT_ROOT'] . '/assets/cmd/',
        'uploader' => $_SERVER['DOCUMENT_ROOT'] . '/jukebox/uploader/'
    ],

    'disc' => [
        'logs' => $_SERVER['DOCUMENT_ROOT'] . '/jukebox/disc/logs/',
        'path' => $_SERVER['DOCUMENT_ROOT'] . '/jukebox/disc/',
        'scripts' => $_SERVER['DOCUMENT_ROOT'] . '/assets/cmd/disc/',
        'status_file' => $_SERVER['DOCUMENT_ROOT'] . '/jukebox/disc/status.json',
        'burner' => [
            'logs' => $_SERVER['DOCUMENT_ROOT'] . '/jukebox/disc/burner/logs/',
            'input' => $_SERVER['DOCUMENT_ROOT'] . '/jukebox/disc/burner/input/',
            'output' => $_SERVER['DOCUMENT_ROOT'] . '/jukebox/disc/burner/output/'
        ],
        'ripper' => [
            'cdparanoia_log' => $_SERVER['DOCUMENT_ROOT'] . '/jukebox/disc/logs/ripper/cdparanoia.log',
            'lame_log' => $_SERVER['DOCUMENT_ROOT'] . '/jukebox/disc/logs/ripper/lame.log',
            'input' => $_SERVER['DOCUMENT_ROOT'] . '/jukebox/disc/ripper/input/',
            'output' => $_SERVER['DOCUMENT_ROOT'] . '/jukebox/disc/ripper/output/'
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
