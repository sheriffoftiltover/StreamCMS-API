<?php

declare(strict_types=1);

namespace Database\StreamCMS;

use AbstractDoctrineModel;
use Database\Relational\AbstractDoctrineDatabase;

abstract class StreamCMSModel extends AbstractDoctrineModel
{
    public static function getDatabase(): AbstractDoctrineDatabase
    {
        return StreamCMSDB::get();
    }
}
