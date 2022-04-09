<?php

declare(strict_types=1);

namespace StreamCMS\Database\StreamCMS;

use StreamCMS\Core\Database\Relational\AbstractDoctrineDatabase;
use StreamCMS\Core\Database\Relational\Config\AbstractDoctrineDBConfig;

final class StreamCMSDB extends AbstractDoctrineDatabase
{
    public function getConfig(): AbstractDoctrineDBConfig
    {
        return new StreamCMSDBConfig();
    }
}
