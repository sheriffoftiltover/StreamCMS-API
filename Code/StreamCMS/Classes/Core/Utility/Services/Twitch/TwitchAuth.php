<?php

declare(strict_types=1);

namespace StreamCMS\Core\Utility\Services\Twitch;

class TwitchAuth
{
    public function __construct(
        private string $accessToken,
        private int $expiresIn,
        private string $refreshToken,
        private array $scope,
        private string $tokenType,
    )
    {
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getScope(): array
    {
        return $this->scope;
    }

    public function getTokenType(): string
    {
        return $this->tokenType;
    }
}
