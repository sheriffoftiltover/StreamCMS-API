<?php

declare(strict_types=1);

use StreamCMS\Site\Models\Site;
use StreamCMS\User\Models\Account;
use StreamCMS\Utility\API\Tokens\IdentityRefreshToken;

require '../../StreamCMSInit.php';

$account = Account::getOneBy(['name' => 'sheriffoftiltover']);
$site = Site::getOneBy(['host' => 'streamcms.dev']);
$token = (new IdentityRefreshToken($account, $site))->create();
dump($token);