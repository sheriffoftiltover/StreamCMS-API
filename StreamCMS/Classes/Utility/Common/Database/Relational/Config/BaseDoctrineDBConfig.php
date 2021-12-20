<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Common\Database\Relational\Config;

abstract class BaseDoctrineDBConfig extends AbstractDoctrineDBConfig
{
    public function getProxyDirectory(): string
    {
        return STREAM_CMS_DIR . '/Classes/Utility/Common/Models/Proxies';
    }

    public function getProxyNamespace(): string
    {
        return 'StreamCMS\\Utility\\Common\\Models\\Proxies';
    }

    public function getEventSubscribers(): array
    {
        return [];
    }
}
