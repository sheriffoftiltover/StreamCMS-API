<?php

declare(strict_types=1);

namespace StreamCMS\Database\StreamCMS;

use StreamCMS\Utility\Database\Relational\AbstractDoctrineDatabase;
use StreamCMS\Utility\Database\Relational\Config\AbstractDoctrineDBConfig;

final class StreamCMSDB extends AbstractDoctrineDatabase
{
    public function getConfig(): AbstractDoctrineDBConfig
    {
        return new StreamCMSDBConfig();
    }
}
