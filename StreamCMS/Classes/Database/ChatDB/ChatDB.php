<?php

declare(strict_types=1);

namespace StreamCMS\Database\ChatDB;

use StreamCMS\Utility\Database\Relational\AbstractDoctrineDatabase;
use StreamCMS\Utility\Database\Relational\Config\AbstractDoctrineDBConfig;

final class ChatDB extends AbstractDoctrineDatabase
{
    public function getConfig(): AbstractDoctrineDBConfig
    {
        return new ChatDBConfig();
    }
}
