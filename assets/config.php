<?php

return [

    /**
     * |----------------------------------------------
     * | Application Name
     * |----------------------------------------------
     * |The name that will be shown in the page title.
     */
    'name' => "Jukebox",

    /**
     * |----------------------------------------------
     * | Application FQDN
     * |----------------------------------------------
     * |The FQDN of this jukebox.
     */
    'fqdn' => 'Jukebox',

    /**
     * |----------------------------------------------
     * | Assets absolute path
     * |----------------------------------------------
     * |The path to the assets directory.
     */
    'assets_path' => $_SERVER['DOCUMENT_ROOT'] . '/assets',

    /**
     * |----------------------------------------------
     * | Application URL
     * |----------------------------------------------
     * |The URL used to access the application.
     */
    'url' => 'http://localhost',

    /**
     * |----------------------------------------------
     * | Application Debug Mode
     * |----------------------------------------------
     * |The application debug mode.
     */
    'debug' => true
];
