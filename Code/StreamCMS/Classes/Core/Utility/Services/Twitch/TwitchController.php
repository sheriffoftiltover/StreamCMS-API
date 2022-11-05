<?php

declare(strict_types=1);

namespace StreamCMS\Core\Utility\Services\Twitch;

class TwitchController
{
    private TwitchClient $twitchClient;
    private TwitchAuth|null $twitchAuth = null;

    public function __construct()
    {
        $this->twitchClient = new TwitchClient();
    }

    public function setTwitchAuth(string $authCode, string $grantType): void
    {
        $this->twitchAuth = new TwitchAuth(...$this->twitchClient->getAccessToken($authCode, $grantType));
    }

    public function getTwitchAuth(): TwitchAuth
    {
        return $this->twitchAuth;
    }

    public function getTwitchUser(): TwitchUser
    {
        return new TwitchUser(...$this->twitchClient->getUser($this->getTwitchAuth()));
    }
}
