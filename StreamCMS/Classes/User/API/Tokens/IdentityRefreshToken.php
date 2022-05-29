<?php

declare(strict_types=1);

namespace StreamCMS\User\API\Tokens;

use StreamCMS\Core\API\Tokens\AbstractToken;
use StreamCMS\Core\Security\Tokens\AbstractJWT;
use StreamCMS\Core\Security\Tokens\IdentityRefreshJWT;
use StreamCMS\User\Models\Account;

final class IdentityRefreshToken extends AbstractToken
{
    public static function create(Account $account): string
    {
        // TODO:
        //  Check if token for particular user exists in redis
        //  If it does and the ttl is > 10 seconds
        //  Return to user
        //  Else Create a new token and store for TTL
        //  Return token to user
        return self::getJWT()->encode(
            [
                'accountId' => $account->getId(),
            ],
            self::getJWT()->getMaxExpirationTime(),
        );
    }

    public static function validate(string $token): array
    {
        return self::getJWT()->decode($token);
    }

    protected static function newJWT(): AbstractJWT
    {
        return new IdentityRefreshJWT();
    }
}