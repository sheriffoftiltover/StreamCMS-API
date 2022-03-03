<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use StreamCMS\Config\LogConfig;
use StreamCMS\Site\Models\Site;
use StreamCMS\User\Models\Account;
use StreamCMS\API\Tokens\IdentityRefreshToken;
use StreamCMS\Utility\Logging\LogUtil;

require '../../StreamCMSInit.php';

$authCode = 'dv15xgn6d6ay43n3pzjis6yjdkc16f';
$grantType = 'authorization_code';


$client = new Client([
    'base_uri' => 'https://id.twitch.tv'
]);
$response = $client->post(
    '/oauth2/token',
    [
        'json' => [
            'client_id' => $_ENV['TWITCH_CLIENT_ID'],
            'client_secret' => $_ENV['TWITCH_CLIENT_SECRET'],
            'code' => $authCode,
            'grant_type' => $grantType,
            'redirect_uri' => $_ENV['TWITCH_REDIRECT_URL'],
        ]
    ]
);
dump($response->getBody()->getContents());