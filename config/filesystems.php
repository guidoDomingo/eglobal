<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. A "local" driver, as well as a variety of cloud
    | based drivers are available for your choosing. Just store away!
    |
    | Supported: "local", "ftp", "s3", "rackspace"
    |
    */

    'default' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => 's3',

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app'),
        ],

        'ftp' => [
            'driver'   => 'ftp',
            'host'     => 'ftp.example.com',
            'username' => 'your-username',
            'password' => 'your-password',

            // Optional FTP Settings...
            // 'port'     => 21,
            // 'root'     => '',
            // 'passive'  => true,
            // 'ssl'      => true,
            // 'timeout'  => 30,
        ],

        's3' => [
            'driver' => 's3',
            'key'    => 'your-key',
            'secret' => 'your-secret',
            'region' => 'your-region',
            'bucket' => 'your-bucket',
        ],

        'rackspace' => [
            'driver'    => 'rackspace',
            'username'  => 'your-username',
            'key'       => 'your-key',
            'container' => 'your-container',
            'endpoint'  => 'https://identity.api.rackspacecloud.com/v2.0/',
            'region'    => 'IAD',
            'url_type'  => 'publicURL',
        ],

        'marcas_servicios' => [
            'driver' => 'local',
            'root' => public_path().'/resources/images/button',
            'visibility' => 'public',
            'url' => env('APP_URL').'/public'
        ],

        'contenidos' => [
            'driver' => 'local',
            'root' => public_path().'/resources/images/contents',
            'visibility' => 'public',
            'url' => env('APP_URL').'/public'
        ],
        
        'artes' => [
            'driver' => 'local',
            'root' => public_path().'/resources/images/arts',
            'visibility' => 'public',
            'url' => env('APP_URL').'/public'
        ],

        'boleta_deposito' => [
            'driver' => 'local',
            'root' => public_path().'/resources/images/boleta_deposito',
            'visibility' => 'public',
            'url' => env('APP_URL').'/public'
        ],

        'branches_promociones' => [
            'driver' => 'local',
            'root' => public_path().'/resources/images/branches_promotions',
            'visibility' => 'public',
            'url' => env('APP_URL').'/public'
        ],

        
        'baja_retiro_dispositivo' => [
            'driver' => 'local',
            'root' => public_path().'/resources/images/retiro_dispositivo',
            'visibility' => 'public',
            'url' => env('APP_URL').'/public'
        ],

        'baja_recibos' => [
            'driver' => 'local',
            'root' => public_path().'/resources/images/baja_recibos',
            'visibility' => 'public',
            'url' => env('APP_URL').'/public'
        ]
    ],

];
