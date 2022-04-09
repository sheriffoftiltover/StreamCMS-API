<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Models;

use StreamCMS\Core\Database\Relational\AbstractDoctrineDatabase;
use StreamCMS\Core\Exceptions\Database\ModelNotFoundException;

abstract class AbstractDoctrineModel
{
//    abstract public function toArray(): array;

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

    public function persist(): void
    {
        static::getDatabase()->getEntityManager()->persist($this);
    }

    public function flush(bool $flushAll = false): void
    {
        static::getDatabase()->getEntityManager()->flush(!$flushAll ? $this : null);
    }

    public function save(bool $flushAll = false): static
    {
        $this->persist();
        $this->flush($flushAll);
        return $this;
    }
}
