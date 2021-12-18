<?php

declare(strict_types=1);

namespace StreamCMS\Database\StreamCMS;

use StreamCMS\Utility\Common\Database\Relational\AbstractDoctrineDatabase;
use StreamCMS\Utility\Common\Models\AbstractDoctrineModel;

abstract class StreamCMSModel extends AbstractDoctrineModel
{
    public static function getDatabase(): AbstractDoctrineDatabase
    {
        return StreamCMSDB::get();
    }
}
