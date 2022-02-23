<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Database\KeyValue\Config;

abstract class AbstractRedisConfig
{
    abstract public function getHost(): string;

    abstract public function getDatabase(): int;

    abstract public function getPort(): int;
}