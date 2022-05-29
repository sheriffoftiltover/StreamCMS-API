<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use StreamCMS\Core\Logging\LogUtil;
use StreamCMS\Site\Models\Site;
use StreamCMS\User\API\Tokens\IdentityRefreshToken;
use StreamCMS\User\Controllers\AccountProviders\TwitchAccountProvider;
use StreamCMS\Core\Utility\Services\Twitch\TwitchController;
use StreamCMS\User\Models\Account;

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
//
//$code = 'm9ehftxd35osiqjvbr4hr33u8xx4a1';
//$scope = 'user_read';
//
//$twitchController = new TwitchController();
//$twitchController->setTwitchAuth($code, 'authorization_code');
//$account = (new TwitchAccountProvider($twitchController->getTwitchUser()))->getAccount();
//
//$site = Site::findOneBy(['host' => 'streamcms.dev']);
//$token = (new TwitchController($account, $site))->getRefreshToken();
//dump($token);

//$account = Account::getOneBy(['email' => 'sheriffoftiltover@hotmail.com']);
//dump('Wtf ' . IdentityRefreshToken::create($account));