<?php

declare(strict_types=1);

namespace StreamCMS\Chat\Database;

use StreamCMS\Utility\Common\Database\Relational\AbstractDoctrineDatabase;
use StreamCMS\Utility\Common\Database\Relational\Config\AbstractDoctrineDBConfig;

final class ChatDB extends AbstractDoctrineDatabase
{
    public function getConfig(): AbstractDoctrineDBConfig
    {
        return new ChatDBConfig();
    }
}
