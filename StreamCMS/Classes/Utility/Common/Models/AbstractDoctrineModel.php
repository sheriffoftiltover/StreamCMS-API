<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Common\Models;

use StreamCMS\Utility\Common\Database\Relational\AbstractDoctrineDatabase;
use StreamCMS\Utility\Common\Exceptions\Database\ModelNotFoundException;

abstract class AbstractDoctrineModel
{
    abstract public static function getDatabase(): AbstractDoctrineDatabase;

    public static function getEntityName(): string
    {
        return substr(strrchr(static::class, '\\'), 1);
    }

    public static function get(int|string $primaryKey): static
    {
        return static::getDatabase()->getEntityManager()->find(
                static::class,
                $primaryKey
            ) ?? throw new ModelNotFoundException(
                static::getEntityName(), [
                    'id' => $primaryKey,
                ],
            );
    }

    public static function getBy(array $criteria = [], array $orderBy = []): array
    {
        return static::getDatabase()->getEntityManager()->getRepository(static::class)->findBy($criteria, $orderBy);
    }

    public static function findOneBy(array $criteria, array $orderBy = []): static|null
    {
        try {
            return static::getDatabase()->getEntityManager()->getRepository(static::class)->findBy(
                    $criteria,
                    $orderBy,
                    1
                )[0] ?? null;
        } catch (\Exception) {
        }
        return null;
    }

    public static function getOneBy(array $criteria, array $orderBy = []): static
    {
        return static::findOneBy($criteria, $orderBy) ?? throw new ModelNotFoundException(
                static::getEntityName(),
                $criteria
            );
    }


    public function refresh(): void
    {
        static::getDatabase()->getEntityManager()->refresh($this);
    }
}
