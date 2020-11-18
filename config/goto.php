<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Debug mode.
    |--------------------------------------------------------------------------
    |
    | To debug what happens, set this to true. Default false.
    |
    */

    'debug' => env('GOTO_DEBUG', false),


    /*
    |--------------------------------------------------------------------------
    | Persistent storage.
    |--------------------------------------------------------------------------
    |
    | Configure disk and file for persistent storage of all access information
    | retrieved.
    |
    */

    'storage' => [
        'disk' => env('GOTO_STORAGE_DISK', 'local'),
        'path' => env('GOTO_STORAGE_PATH', 'goto'),
        'basename' => env('GOTO_STORAGE_BASENAME', 'access')
    ],


    /*
    |--------------------------------------------------------------------------
    | Connections.
    |--------------------------------------------------------------------------
    |
    | LogMeIn connections.
    |
    */

    'connections' => [
        'default' => [

            /*
            |--------------------------------------------------------------------------
            | OAuth credentials.
            |--------------------------------------------------------------------------
            |
            | The OAuth credentials. Read https://developer.goto.com/guides/HowTos/02_HOW_createClient/
            | for more information.
            |
            */

            'client_id' => env('GOTO_CONSUMER_KEY', null),
            'client_secret' => env('GOTO_CONSUMER_SECRET', null),

            /*
            |--------------------------------------------------------------------------
            | Direct Login.
            |--------------------------------------------------------------------------
            | @deprecated
            | Direct Login is not available for newly created OAuth clients since
            | 2020-07-01. In order to support legacy clients, this option is still
            | available. By default legacy support is turned off.
            |
            */

            'legacy' => env('GOTO_LEGACY', false),
            'username' => env('GOTO_USERNAME', env('GOTO_DIRECT_USERNAME', null)),
            'password' => env('GOTO_PASSWORD', env('GOTO_DIRECT_PASSWORD', null)),


            /*
            |--------------------------------------------------------------------------
            | Authorization code.
            |--------------------------------------------------------------------------
            |
            | An authorization code can be required by generating a login link combined
            | with a redirect URI. The redirect URI needs to match the specified redirect
            | URI in the created OAuth client.
            |
            */

            'authorization_code' => env('GOTO_AUTHORIZATION_CODE', null),
            'redirect_uri' => env('GOTO_REDIRECT_URI', null),
        ]

        // .. add many more connections
    ],

    /*
    |--------------------------------------------------------------------------
    | Specific settings.
    |--------------------------------------------------------------------------
    |
    | Specific GoToWebinar API settings.
    |
    */

    'subject_suffix' => env('GOTO_SUBJECT_SUFFIX', null),
    'webinar_link' => env('GOTO_WEBINAR_LINK', 'https://global.gotowebinar.com/manageWebinar.tmpl?webinar=%s'),


    /*
    |--------------------------------------------------------------------------
    | HTTP Settings.
    |--------------------------------------------------------------------------
    |
    | HTTP Settings.
    |
    */

    'http' => [
        'timeout' => 10 // seconds
    ]

];
