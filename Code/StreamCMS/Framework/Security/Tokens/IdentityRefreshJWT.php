<?php

declare(strict_types=1);

namespace Security\Tokens;

class IdentityRefreshJWT extends AbstractJWT
{
    protected function getSecret(): string
    {
        return $_ENV['IDENTITY_REFRESH_TOKEN_SECRET'];
    }

    protected function getMaxAge(): int
    {
        return 14 * 86400;
    }

    protected function getAllowedSkew(): int
    {
        return 0;
    }
}
