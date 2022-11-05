<?php

declare(strict_types=1);

namespace Database\StreamCMS;

use Database\KeyValue\Config\AbstractRedisConfig;

final class StreamCMSDBRedisConfig extends AbstractRedisConfig
{
    public function getHost(): string
    {
        return $_ENV['STREAM_CMS_REDIS_HOST'];
    }

    public function getDatabase(): int
    {
        return (int) $_ENV['STREAM_CMS_REDIS_DATABASE'];
    }

    public function getPort(): int
    {
        return (int) $_ENV['STREAM_CMS_REDIS_PORT'];
    }
}
