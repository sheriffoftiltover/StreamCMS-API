<?php

declare(strict_types=1);

use Database\StreamCMS\StreamCMSDB;

$initFilePath = __DIR__ . '/../../../StreamCMSInit.php';

require $initFilePath;

$streamCMSDB = StreamCMSDB::get();
$streamCMSDBConfig = $streamCMSDB->getConfig()->getConfigArray();
$connection = new PDO(
    'mysql:unix_socket=/var/lib/mysql/mysql.sock;dbname=' . $streamCMSDBConfig['dbname'],
    $streamCMSDBConfig['user'],
    $streamCMSDBConfig['password'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
    ]
);
return [
    'environments' => [
        'default_database' => 'default',
        'default_migration_table' => 'streamcms_migrations',
        'default' => [
            'name' => $streamCMSDBConfig['dbname'],
            'connection' => $connection,
        ],
    ],
    'paths' => [
        'migrations' => [__DIR__ . '/Migrations'],
        'seeds' => __DIR__ . '/Seeders',
        'bootstrap' => $initFilePath,
    ],
];