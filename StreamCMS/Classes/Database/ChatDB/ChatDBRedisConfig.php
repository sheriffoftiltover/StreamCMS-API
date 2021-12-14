<?php

declare(strict_types=1);

namespace StreamCMS\Database\ChatDB;

use StreamCMS\Utility\Database\KeyValue\Config\AbstractRedisConfig;

final class ChatDBRedisConfig extends AbstractRedisConfig
{
    public function getHost(): string
    {
        return $_ENV['CHAT_DB_REDIS_HOST'];
    }

    public function getDatabase(): int
    {
        return $_ENV['CHAT_DB_REDIS_DATABASE'];
    }

    public function getPort(): int
    {
        return $_ENV['CHAT_DB_REDIS_PORT'];
    }
}
