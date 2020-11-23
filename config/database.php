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

    //'default' => env('DB_CONNECTION', 'mysql_master'),

    //新增oracle支持
    'default' => env('DB_CONNECTION', 'oracle'),

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
        //新增Oracle
        'oracle' => [
            'driver'        => 'oracle',
            'tns'           => env('DB_TNS', ''),
            'host'          => env('DB_HOST', '127.0.0.1'),
            'port'          => env('DB_PORT', '1521'),
            'database'      => env('DB_DATABASE', 'helowin'),
            'username'      => env('DB_USERNAME', 'system'),
            'password'      => env('DB_PASSWORD', 'oracle'),
            'charset'       => env('DB_CHARSET', 'AL32UTF8'),
            'prefix'        => env('DB_PREFIX', ''),
            'prefix_schema' => env('DB_SCHEMA_PREFIX', ''),
        ],

        'mysql_master' => [
            'driver'    => 'mysql',
            'host'      => env('DB_MASTER_HOST', 'localhost'),
            'port'      => env('DB_MASTER_PORT', 3306),
            'database'  => env('DB_MASTER_DATABASE', 'forge'),
            'username'  => env('DB_MASTER_USERNAME', 'forge'),
            'password'  => env('DB_MASTER_PASSWORD', ''),
            'charset'   => env('DB_MASTER_CHARSET', 'utf8mb4'),
            'collation' => env('DB_MASTER_COLLATION', 'utf8mb4_general_ci'),
            'prefix'    => env('DB_MASTER_PREFIX', ''),
            'timezone'  => env('DB_MASTER_TIMEZONE', '+08:00'),
            'strict'    => env('DB_MASTER_STRICT_MODE', true),// 使用mysql严格模式
        ],

        'sndb' => [ //水泥库
            'driver'    => 'mysql',
            'host'      => env('DB_SN_HOST', 'localhost'),
            'port'      => env('DB_SN_PORT', 3306),
            'database'  => env('DB_SN_DATABASE', 'forge'),
            'username'  => env('DB_SN_USERNAME', 'forge'),
            'password'  => env('DB_SN_PASSWORD', ''),
            'charset'   => env('DB_SN_CHARSET', 'utf8mb4'),
            'collation' => env('DB_SN_COLLATION', 'utf8mb4_general_ci'),
            'prefix'    => env('DB_SN_PREFIX', ''),
            'timezone'  => env('DB_SN_TIMEZONE', '+08:00'),
            'strict'    => env('DB_SN_STRICT_MODE', false),// 使用mysql严格模式
        ],
        'jxsdb' => [//经销商库
            'driver'    => 'mysql',
            'host'      => env('DB_JXS_HOST', 'localhost'),
            'port'      => env('DB_JXS_PORT', 3306),
            'database'  => env('DB_JXS_DATABASE', 'forge'),
            'username'  => env('DB_JXS_USERNAME', 'forge'),
            'password'  => env('DB_JXS_PASSWORD', ''),
            'charset'   => env('DB_JXS_CHARSET', 'utf8mb4'),
            'collation' => env('DB_JXS_COLLATION', 'utf8mb4_general_ci'),
            'prefix'    => env('DB_JXS_PREFIX', ''),
            'timezone'  => env('DB_JXS_TIMEZONE', '+08:00'),
            'strict'    => env('DB_JXS_STRICT_MODE', false),// 使用mysql严格模式
        ],

        'wldb' => [//物流库
            'driver'    => 'mysql',
            'host'      => env('DB_WL_HOST', 'localhost'),
            'port'      => env('DB_WL_PORT', 3306),
            'database'  => env('DB_WL_DATABASE', 'forge'),
            'username'  => env('DB_WL_USERNAME', 'forge'),
            'password'  => env('DB_WL_PASSWORD', ''),
            'charset'   => env('DB_WL_CHARSET', 'utf8mb4'),
            'collation' => env('DB_WL_COLLATION', 'utf8mb4_general_ci'),
            'prefix'    => env('DB_WL_PREFIX', ''),
            'timezone'  => env('DB_WL_TIMEZONE', '+08:00'),
            'strict'    => env('DB_WL_STRICT_MODE', false),// 使用mysql严格模式
        ],
        'jrdb' => [//金融库
            'driver'    => 'mysql',
            'host'      => env('DB_JR_HOST', 'localhost'),
            'port'      => env('DB_JR_PORT', 3306),
            'database'  => env('DB_JR_DATABASE', 'forge'),
            'username'  => env('DB_JR_USERNAME', 'forge'),
            'password'  => env('DB_JR_PASSWORD', ''),
            'charset'   => env('DB_JR_CHARSET', 'utf8mb4'),
            'collation' => env('DB_JR_COLLATION', 'utf8mb4_general_ci'),
            'prefix'    => env('DB_JR_PREFIX', ''),
            'timezone'  => env('DB_JR_TIMEZONE', '+08:00'),
            'strict'    => env('DB_JR_STRICT_MODE', false),// 使用mysql严格模式
        ],

        //mongodb服务
        //mongodb://127.0.0.1:27017
        'mongodb' => [
            'driver'   => 'mongodb',
            'host'     => env('DB_HOST_MG', '127.0.0.1'),
            'port'     => env('DB_PORT_MG', 27017),
            'database' => env('DB_DATABASE_MG','testDb'),
            'username' => env('DB_USERNAME_MG',''),
            'password' => env('DB_PASSWORD_MG',''),
        ],
    ],

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

        'client' => 'predis',

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DATABASE', 0),
        ],

        'cache' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 1),
        ],
        'session' => [
            'host'     => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD',null),
            'port'     => env('REDIS_PORT', 6379),
            'database' => 1,
        ]
    ],
];
