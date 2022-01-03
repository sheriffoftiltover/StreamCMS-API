<?php

declare(strict_types=1);

use StreamCMS\Database\StreamCMS\StreamCMSDB;
use StreamCMS\Database\StreamCMS\StreamCMSDBConfig;
use StreamCMS\Site\Models\Site;
use StreamCMS\User\Models\Account;
use StreamCMS\Utility\Classes\ClassUtils;
use StreamCMS\Utility\Common\API\Tokens\IdentityRefreshToken;
use StreamCMS\Utility\Common\Database\Relational\Config\AbstractDBConfig;
use StreamCMS\Utility\Files\FileSystem;

require '../../StreamCMSInit.php';

$account = Account::getOneBy(['name' => 'sheriffoftiltover']);
$site = Site::getOneBy(['host' => 'streamcms.dev']);
$token = (new IdentityRefreshToken($account, $site))->create();
dump($token);