<?php

declare(strict_types=1);

namespace StreamCMS\Core\API\Tokens;

use StreamCMS\Core\Security\Tokens\AbstractJWT;

abstract class AbstractToken
{
    /** @var AbstractJWT[] $jwtInstances */
    protected static array $jwtInstances = [];

    public static function getJWT(): AbstractJWT
    {
        if (! isset(self::$jwtInstances[static::class])) {
            self::$jwtInstances[static::class] = static::newJWT();
        }
        return self::$jwtInstances[static::class];
    }

    abstract protected static function newJWT(): AbstractJWT;
}