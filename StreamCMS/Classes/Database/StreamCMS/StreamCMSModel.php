<?php

declare(strict_types=1);

namespace StreamCMS\Database\StreamCMS;

use StreamCMS\Utility\Database\Relational\AbstractDoctrineDatabase;
use StreamCMS\Utility\Models\AbstractDoctrineModel;

abstract class StreamCMSModel extends AbstractDoctrineModel
{
    public static function getDatabase(): AbstractDoctrineDatabase
    {
        return StreamCMSDB::get();
    }
}
