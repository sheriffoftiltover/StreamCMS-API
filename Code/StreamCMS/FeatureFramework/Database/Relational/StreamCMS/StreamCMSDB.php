<?php

declare(strict_types=1);

namespace Database\StreamCMS;

use Database\Relational\AbstractDoctrineDatabase;
use Database\Relational\Config\AbstractDoctrineDBConfig;

final class StreamCMSDB extends AbstractDoctrineDatabase
{
    public function getConfig(): AbstractDoctrineDBConfig
    {
        return new StreamCMSDBConfig();
    }
}
