<?php

declare(strict_types=1);

namespace StreamCMS\Utility\API\Tokens;

use StreamCMS\Utility\Security\Tokens\AbstractJWT;

class IdentityAccessToken extends AbstractJWT
{
    public function getSecret(): string
    {
        return $_ENV['IDENTITY_ACCESS_TOKEN_SECRET'];
    }

    public function getMaxAge(): int
    {
        return 3600;
    }

    public function getAllowedSkew(): int
    {
        return 0;
    }

    public function validate(string $token): void
    {
        // TODO: Implement validate() method.
    }

    public function create(): string
    {
        // TODO: Implement create() method.
    }
}
