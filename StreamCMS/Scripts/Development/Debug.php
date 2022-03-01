<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use StreamCMS\Config\LogConfig;
use StreamCMS\Site\Models\Site;
use StreamCMS\User\Models\Account;
use StreamCMS\Utility\API\Tokens\IdentityRefreshToken;
use StreamCMS\Utility\Logging\LogUtil;

require '../../StreamCMSInit.php';

$client = new Client([
    'base_uri' => 'https://api.streamcms.dev',
]);
$res = $client->post(
    '/account/register/twitch',
    [
        'json' => [
            'code' => 123,
            'user_scope' => '1231324',
        ]
    ]
);
dump($res->getBody()->getContents());