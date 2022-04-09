<?php

declare(strict_types=1);

namespace StreamCMS\Core\Security\Tokens;

class IdentityAccessJWT extends AbstractJWT
{
    protected function getSecret(): string
    {
        return $_ENV['IDENTITY_ACCESS_TOKEN_SECRET'];
    }

    protected function getMaxAge(): int
    {
        return 3600;
    }

    protected function getAllowedSkew(): int
    {
        return 0;
    }
}
