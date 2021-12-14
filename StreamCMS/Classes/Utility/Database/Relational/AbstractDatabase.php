<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Database\Relational;

use StreamCMS\Utility\Database\Relational\Config\AbstractDBConfig;

abstract class AbstractDatabase
{
    private static AbstractDatabase|null $instance = null;

    public static function get(): static
    {
        static::$instance ??= new static();
        return static::$instance;
    }

    abstract public function getConfig(): AbstractDBConfig;
}
