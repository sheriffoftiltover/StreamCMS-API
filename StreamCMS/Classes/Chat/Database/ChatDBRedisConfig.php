<?php

declare(strict_types=1);

namespace StreamCMS\Chat\Database;

use StreamCMS\Utility\Common\Database\KeyValue\Config\AbstractRedisConfig;

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
