<?php

return [

    /*
     * |----------------------------------------------
     * | Application Name
     * |----------------------------------------------
     * | The name that will be shown in the page title.
     */
    'name' => 'Jukebox',

    /*
     * |----------------------------------------------
     * | Application FQDN
     * |----------------------------------------------
     * | The FQDN of this jukebox.
     */
    'fqdn' => 'Jukebox',

    /*
     * |----------------------------------------------
     * | Application URL
     * |----------------------------------------------
     * | The URL used to access the application.
     */
    'url' => 'http://localhost',

    /*
     * |----------------------------------------------
     * | Application Debug Mode
     * |----------------------------------------------
     * | The application debug mode.
     */
    'debug' => true,

    /*
     * |----------------------------------------------
     * | Paths
     * |----------------------------------------------
     * | Paths to the most important directories.
     */
    'paths' => [
        'document_root'         => $_SERVER['DOCUMENT_ROOT'],
        'albums_root'           => $_SERVER['DOCUMENT_ROOT'].'/jukebox/',
        'downloads_directory'   => $_SERVER['DOCUMENT_ROOT'].'/jukebox/downloads/',
        'tmp_uploads'           => $_SERVER['DOCUMENT_ROOT'].'/jukebox/tmp_uploads/',
        'assets'                => $_SERVER['DOCUMENT_ROOT'].'/assets/',
        'scripts'               => $_SERVER['DOCUMENT_ROOT'].'/assets/cmd/',
        'uploader'              => $_SERVER['DOCUMENT_ROOT'].'/jukebox/uploader/',
        'installation'          => $_SERVER['DOCUMENT_ROOT'].'/installation/',
    ],

    'disc' => [
        'logs'          => $_SERVER['DOCUMENT_ROOT'].'/jukebox/disc/logs/',
        'scripts'       => $_SERVER['DOCUMENT_ROOT'].'/assets/cmd/disc/',
        'status_file'   => $_SERVER['DOCUMENT_ROOT'].'/jukebox/uploader/status.json',
        'ripper'        => [
            'parent'          => $_SERVER['DOCUMENT_ROOT'].'/jukebox/ripper',
            'input'           => $_SERVER['DOCUMENT_ROOT'].'/jukebox/ripper/input/',
            'handler'         => $_SERVER['DOCUMENT_ROOT'].'/assets/cmd/disc/rip_handler.sh',
            'cdparanoia_log'  => $_SERVER['DOCUMENT_ROOT'].'/jukebox/ripper/logs/cdparanoia.log',
            'lame_log'        => $_SERVER['DOCUMENT_ROOT'].'/jukebox/ripper/logs/lame.log',
        ],
    ],

    /*
     * |----------------------------------------------
     * | Default database configuration
     * |----------------------------------------------
     * | Database configuration variables.
     */
    'database' => [
        'host'     => 'localhost',
        'name'     => 'jukebox',
        'user'     => 'root',
        'password' => 'password1000',
    ],

    /*
     * |----------------------------------------------
     * | Ports
     * |----------------------------------------------
     * | Default ports for the various http servers.
     */
    'ports' => [
        'webui'  => 80,
        'remote' => 4202,
        'radio'  => 4242,
    ],
];
