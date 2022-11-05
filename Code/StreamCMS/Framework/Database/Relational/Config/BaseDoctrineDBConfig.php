<?php

declare(strict_types=1);

namespace Database\Relational\Config;

use const STREAM_CMS_DIR;

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
