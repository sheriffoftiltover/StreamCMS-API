<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use StreamCMS\Config\LogConfig;
use StreamCMS\Site\Models\Site;
use StreamCMS\User\Models\Account;
use StreamCMS\API\Tokens\IdentityRefreshToken;
use StreamCMS\Utility\Logging\LogUtil;
use StreamCMS\Utility\Services\Twitch\TwitchController;

require '../../StreamCMSInit.php';

$authCode = 'c3868qt44uarwp0oqdydvihrva1356';
$grantType = 'authorization_code';

$twitchController = new TwitchController();
$twitchController->setTwitchAuth($authCode, $grantType);
dump($twitchController->getTwitchUser());