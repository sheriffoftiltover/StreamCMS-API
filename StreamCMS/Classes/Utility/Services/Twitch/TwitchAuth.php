<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Services\Twitch;

class TwitchAuth
{
    public function __construct(
        private string $access_token,
        private int $expires_in,
        private string $refresh_token,
        private array $scope,
        private string $token_type,
    )
    {
    }

    public function getAccessToken(): string
    {
        return $this->access_token;
    }

    public function getExpiresIn(): int
    {
        return $this->expires_in;
    }

    public function getRefreshToken(): string
    {
        return $this->refresh_token;
    }

    public function getScope(): array
    {
        return $this->scope;
    }

    public function getTokenType(): string
    {
        return $this->token_type;
    }
}
