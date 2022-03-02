<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Services\Twitch;

class TwitchController
{
    public function __construct()
    {

    }

    public function getAccessToken(string $authCode, string $grantType): array
    {
        return $this->getResponseData(
            $this->client->post(
                '/oauth2/token',
                [
                    'json' => [
                        'client_id' => $this->getClientId(),
                        'client_secret' => $this->getClientSecret(),
                        'code' => $authCode,
                        'grant_type' => $grantType,
                        'redirect_uri' => $this->getRedirectUrl(),
                    ]
                ]
            ),
        );
    }
}
