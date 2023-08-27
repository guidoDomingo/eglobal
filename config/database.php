<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PDO Fetch Style
    |--------------------------------------------------------------------------
    |
    | By default, database results will be returned as instances of the PHP
    | stdClass object; however, you may desire to retrieve records in an
    | array format for simplicity. Here you can tweak the fetch style.
    |
    */

    'fetch' => PDO::FETCH_CLASS,

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => 'eglobal',

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => database_path('database.sqlite'),
            'prefix'   => '',
        ],

        'mysql' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', 'localhost'),
            'database'  => env('DB_DATABASE', 'forge'),
            'username'  => env('DB_USERNAME', 'forge'),
            'password'  => env('DB_PASSWORD', ''),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
        ],

        'eglobal' => [
            'driver'   => 'pgsql',
            'host'     => env('DB_HOST'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => 'public',
        ],

        'eglobalt_auth' => [
            'driver'   => 'pgsql',
            'host'     => env('AUTH_HOST'),
            'database' => env('AUTH_DATABASE'),
            'username' => env('AUTH_USERNAME'),
            'password' => env('AUTH_PASSWORD'),
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => 'public',
        ],

        'eglobalt_pro' => [
            'driver'   => 'pgsql',
            'host'     => env('PRO_HOST'),
            'database' => env('PRO_DATABASE'),
            'username' => env('PRO_USERNAME'),
            'password' => env('PRO_PASSWORD'),
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => 'public',
        ],

        'eglobalt_replica' => [
            'driver'   => 'pgsql',
            'host'     => env('REPLICA_HOST'),
            'port'     => env('REPLICA_PORT'),
            'database' => env('REPLICA_DATABASE'),
            'username' => env('REPLICA_USERNAME'),
            'password' => env('REPLICA_PASSWORD'),
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => 'public',
        ],

        'sqlsrv' => [
            'driver'   => 'sqlsrv',
            'host'     => env('DB_HOST', 'localhost'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset'  => 'utf8',
            'prefix'   => '',
        ],

        'testing' => [
            'driver'   => 'sqlsrv',
            'host'     => env('TESTING_HOST', 'localhost'),
            'database' => env('TESTING_DATABASE', 'forge'),
            'username' => env('TESTING_USERNAME', 'forge'),
            'password' => env('TESTING_PASSWORD', ''),
            'charset'  => 'utf8',
            'prefix'   => '',
        ],

        'ondanet' => [
            'driver'   => 'sqlsrv',
            'host'     => env('ONDANET_HOST', 'localhost'),
            'port'     => env('ONDANET_PORT'),
            'database' => env('ONDANET_DATABASE', 'forge'),
            'username' => env('ONDANET_USERNAME', 'forge'),
            'password' => env('ONDANET_PASSWORD', ''),
            'charset'  => 'utf8',
            'prefix' => '',
        ],

        'ondanet_antell' => [
            'driver'   => 'sqlsrv',
            'host'     => '192.168.2.97',
            //'port'     => '1433',
            'database' => 'ANTELL',
            'username' => 'EGLOBAL',
            'password' => 'e$1234567',
            'charset'  => 'utf8',
            'prefix' => '',
        ],

        'testing_antell' => [
            'driver'   => 'sqlsrv',
            'host'     => '192.168.2.97\TESTING',
            //'port'     => '1433',
            'database' => 'BACKUP_CONTABILIDAD',
            'username' => 'EGLOBAL',
            'password' => 'e$1234567',
            'charset'  => 'utf8',
            'prefix' => '',
        ],

        'pgsql_ussd' => [
            'driver'   => 'pgsql',
            'host'     => env('USSD_HOST'),
            'port'     => env('USSD_PORT'),
            'database' => env('USSD_DATABASE'),
            'schema'   => env('USSD_SCHEMA'),
            'username' => env('USSD_USERNAME'),
            'password' => env('USSD_PASSWORD'),
            'charset'  => 'utf8',
            'prefix'   => '',
        ]

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'cluster' => false,

        'default' => [
            'host'     => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD', null),
            'port'     => env('REDIS_PORT', 6379),
            'database' => 0,
        ],

    ],

];
