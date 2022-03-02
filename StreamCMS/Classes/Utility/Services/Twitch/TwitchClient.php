<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Services\Twitch;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class TwitchClient
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://id.twitch.tv'
        ]);
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
        return json_decode($response->getBody()->getContents(), true, JSON_THROW_ON_ERROR);
    }
}
