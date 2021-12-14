<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Database\Relational\Config;

use StreamCMS\Utility\Database\KeyValue\Config\AbstractRedisConfig;

abstract class AbstractDoctrineDBConfig extends AbstractDBConfig
{
    abstract public function getRedisConfig(): AbstractRedisConfig;

    abstract public function getModelPaths(): array;

    abstract public function getProxyDirectory(): string;

    abstract public function getProxyNamespace(): string;

    abstract public function getAutogenerateProxyConfiguration(): int;
}
