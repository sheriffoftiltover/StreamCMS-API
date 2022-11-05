<?php

declare(strict_types=1);

require __DIR__ . '/../../../StreamCMSInit.php';

use Database\StreamCMS\StreamCMSDB;
use Database\StreamCMS\StreamCMSDBConfig;
use Doctrine\ORM\Tools\SchemaTool;

StreamCMSDB::get()->getPDO()->exec("DROP DATABASE {$_ENV['STREAM_CMS_DB_NAME']}");
StreamCMSDB::get()->getPDO()->exec("CREATE DATABASE {$_ENV['STREAM_CMS_DB_NAME']}");
StreamCMSDB::reset();
$em = StreamCMSDB::get()->getEntityManager();
$tool = new SchemaTool($em);
$classMetadata = [];
$classes = (new StreamCMSDBConfig())->getModelClasses();
foreach ($classes as $class) {
    $classMetadata[] = $em->getClassMetadata($class);
}
$tool->createSchema($classMetadata);