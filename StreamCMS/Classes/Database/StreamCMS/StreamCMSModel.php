<?php

declare(strict_types=1);

namespace StreamCMS\Database\StreamCMS;

use StreamCMS\Core\Database\Relational\AbstractDoctrineDatabase;
use StreamCMS\Core\Models\AbstractDoctrineModel;

abstract class StreamCMSModel extends AbstractDoctrineModel
{
    public static function getDatabase(): AbstractDoctrineDatabase
    {
        return StreamCMSDB::get();
    }
}
