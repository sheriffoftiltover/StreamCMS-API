<?php

declare(strict_types=1);

use StreamCMS\Database\StreamCMS\StreamCMSDB;
use StreamCMS\Database\StreamCMS\StreamCMSDBConfig;
use StreamCMS\Utility\Classes\ClassUtils;
use StreamCMS\Utility\Common\Database\Relational\Config\AbstractDBConfig;
use StreamCMS\Utility\Files\FileSystem;

require '../../StreamCMSInit.php';

StreamCMSDB::get()->getPDO()->exec('SHOW TABLES');