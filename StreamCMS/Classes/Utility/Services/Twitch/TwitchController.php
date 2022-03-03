<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Services\Twitch;

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

    public function getTwitchUser(): array
    {
        // TODO: This is what the data looks like :D
        /*
            array:1 [
              "data" => array:1 [
                0 => array:11 [
                  "id" => "661415316"
                  "login" => "sheriffoftiltover"
                  "display_name" => "sheriffoftiltover"
                  "type" => ""
                  "broadcaster_type" => ""
                  "description" => ""
                  "profile_image_url" => "https://static-cdn.jtvnw.net/user-default-pictures-uv/998f01ae-def8-11e9-b95c-784f43822e80-profile_image-300x300.png"
                  "offline_image_url" => ""
                  "view_count" => 3
                  "email" => ""
                  "created_at" => "2021-03-13T03:32:45Z"
                ]
              ]
            ]
         */
        return $this->twitchClient->getUser($this->getTwitchAuth());
    }
}
