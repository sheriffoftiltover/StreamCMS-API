<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Environment;

final class Env
{
    public static function isDev(): bool
    {
        return $_ENV['ENVIRONMENT'] === 'development';
    }

    public static function isProd(): bool
    {
        return $_ENV['ENVIRONMENT'] === 'production';
    }

    public static function isCLI(): bool
    {
        return ($_SERVER['SERVER_SOFTWARE'] ?? null) === null;
    }
}
