<?php

$document_root = __DIR__.'/../../';

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
        'albums_root'           => $document_root.'jukebox/',
        'assets'                => $document_root.'assets/',
        'config_directory'      => $document_root.'assets/config/',
        'document_root'         => $document_root,
        'downloads_directory'   => $document_root.'jukebox/downloads/',
        'installation'          => $document_root.'installation/',
        'scripts'               => $document_root.'assets/cmd/',
        'tmp_uploads'           => $document_root.'jukebox/tmp_uploads/',
        'updater'               => $document_root.'updater/',
        'uploader'              => $document_root.'jukebox/uploader/',
    ],

    'disc' => [
        'logs'          => $document_root.'jukebox/disc/logs/',
        'scripts'       => $document_root.'assets/cmd/disc/',
        'status_file'   => $document_root.'jukebox/uploader/status.json',
        'ripper'        => [
            'parent'          => $document_root.'jukebox/ripper',
            'input'           => $document_root.'jukebox/ripper/input/',
            'handler'         => $document_root.'assets/cmd/disc/rip_handler.sh',
            'cdparanoia_log'  => $document_root.'jukebox/ripper/logs/cdparanoia.log',
            'lame_log'        => $document_root.'jukebox/ripper/logs/lame.log',
        ],
    ],

    'phinx' => [
        'default_db'  => 'production',
        'config'      => $document_root.'phinx.yml',
        'bin'         => $document_root.'vendor/bin/phinx',
        'migrations'  => $document_root.'db/migrations',
        'seeds'       => $document_root.'db/seeds',
    ],

    /*
     * |----------------------------------------------
     * | Default database configuration
     * |----------------------------------------------
     * | Database configuration variables.
     */
    'database' => [
        'host'          => 'localhost',
        'name'          => 'jukebox',
        'user'          => 'root',
        'password'      => 'password1000',
    ],

    /*
     * |----------------------------------------------
     * | Ports
     * |----------------------------------------------
     * | Default ports for the various jukebox services.
     */
    'ports' => [
        'http'   => 8080,
        'ssh'    => 22,
        'remote' => 4202,
        'radio'  => 4242,
    ],
];
