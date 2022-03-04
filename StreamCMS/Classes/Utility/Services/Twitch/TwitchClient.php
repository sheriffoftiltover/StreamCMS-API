<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Services\Twitch;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use StreamCMS\Utility\Data\Arrays;

class TwitchClient
{
    private Client $idClient;
    private Client $apiClient;

    public function __construct()
    {
        $this->idClient = new Client([
            'base_uri' => 'https://id.twitch.tv'
        ]);
        $this->apiClient = new Client([
            'base_uri' => 'https://api.twitch.tv'
        ]);
    }

    public function getAccessToken(string $authCode, string $grantType): array
    {
        return $this->getResponseData(
            $this->idClient->post(
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

    public function getUser(TwitchAuth $twitchAuth): array
    {
        return $this->getResponseData(
            $this->apiClient->get(
                '/helix/users',
                [
                    'headers' => [
                        'Authorization' => "Bearer {$twitchAuth->getAccessToken()}",
                        'Client-Id' => $this->getClientId(),
                    ],
                ]
            )
        );
    }

    private function getRedirectUrl(): string
    {
        return $_ENV['TWITCH_REDIRECT_URL'];
    }

    private function getClientId(): string
    {
        return $_ENV['TWITCH_CLIENT_ID'];
    }

    private function getClientSecret(): string
    {
        return $_ENV['TWITCH_CLIENT_SECRET'];
    }

    private function getResponseData(ResponseInterface $response): array
    {
        return Arrays::camelCaseKeys(json_decode($response->getBody()->getContents(), true, JSON_THROW_ON_ERROR));
    }
}
