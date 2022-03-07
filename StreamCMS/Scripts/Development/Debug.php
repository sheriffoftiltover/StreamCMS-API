<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use StreamCMS\Site\Models\Site;
use StreamCMS\User\Controllers\AccountProviders\TwitchAccountProvider;
use StreamCMS\User\Controllers\Authentication\RefreshTokenController;
use StreamCMS\Utility\Services\Twitch\TwitchController;

require '../../StreamCMSInit.php';

//$authCode = 'c3868qt44uarwp0oqdydvihrva1356';
//$grantType = 'authorization_code';
//
//$twitchController = new TwitchController();
//$twitchController->setTwitchAuth($authCode, $grantType);
//dump($twitchController->getTwitchUser());

//dump(Site::findOneBy(['host' => 'streamcms.dev']));
//
//exit;

$code = 'l32lp5tpj2mwfa063ev5627isetlyl';
$scope = 'user_read';

$twitchController = new TwitchController();
$twitchController->setTwitchAuth($code, 'authorization_code');
$account = (new TwitchAccountProvider($twitchController->getTwitchUser()))->getAccount();
$site = Site::findOneBy(['host' => 'streamcms.dev']);
$token = (new RefreshTokenController($account, $site))->getRefreshToken();
dump($token);