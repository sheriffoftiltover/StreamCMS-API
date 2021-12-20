<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Common\Database\Relational;

use StreamCMS\Utility\Common\Database\Relational\Config\AbstractDBConfig;

abstract class AbstractDatabase
{
    private static AbstractDatabase|null $instance = null;

    public static function get(): static
    {
        static::$instance ??= new static();
        return static::$instance;
    }

    public static function reset(): void
    {
        static::$instance = null;
    }

    abstract public function getConfig(): AbstractDBConfig;
}
