<?php

declare(strict_types=1);

namespace StreamCMS\Chat\Database;

use Doctrine\Common\Proxy\AbstractProxyFactory;
use JetBrains\PhpStorm\Pure;
use StreamCMS\Utility\Common\Database\Relational\Config\BaseDoctrineDBConfig;

final class ChatDBConfig extends BaseDoctrineDBConfig
{
    public function getName(): string
    {
        return $_ENV['CHAT_DB_NAME'];
    }

    public function getUser(): string
    {
        return $_ENV['CHAT_DB_USER'];
    }

    public function getPass(): string
    {
        return $_ENV['CHAT_DB_PASS'];
    }

    public function getHost(): string
    {
        return $_ENV['CHAT_DB_HOST'];
    }

    public function getDriver(): string
    {
        return 'pdo_mysql';
    }

    public function getCharset(): string
    {
        return 'utf8mb4';
    }

    public function getModelPaths(): array
    {
        return [
            STREAM_CMS_DIR . '/Classes/Chat/Models',
        ];
    }

    public function getAutogenerateProxyConfiguration(): int
    {
        // TODO: Update this to AUTOGENERATE_NEVER if/when tooling to generate them is created
        return AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS;
    }

    #[Pure]
    public function getRedisConfig(): ChatDBRedisConfig
    {
        return new ChatDBRedisConfig();
    }
}