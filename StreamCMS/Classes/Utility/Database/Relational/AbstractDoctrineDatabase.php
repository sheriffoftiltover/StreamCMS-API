<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Database\Relational;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use StreamCMS\Utility\Database\Relational\Config\AbstractDoctrineDBConfig;

abstract class AbstractDoctrineDatabase extends AbstractDatabase
{
    private EntityManager $entityManager;

    public function __construct()
    {
        $databaseConfig = $this->getConfig();
        $redisConfig = $databaseConfig->getRedisConfig();
        $redis = new \Redis();
        $redis->connect(
            $redisConfig->getHost(),
            $redisConfig->getPort(),
        );
        $redis->select($redisConfig->getDatabase());
        $redisCache = new RedisCache();
        $redisCache->setRedis($redis);
        $cache = new ChainCache(
            [
                new ArrayCache(),
                $redisCache,
            ]
        );

        $entityManagerConfiguration = new Configuration();
        // Setup the cache
        $entityManagerConfiguration->setResultCacheImpl($cache);
        $entityManagerConfiguration->setQueryCacheImpl($cache);
        // Set the proxy directory
        $entityManagerConfiguration->setProxyDir($databaseConfig->getProxyDirectory());
        $entityManagerConfiguration->setProxyNamespace($databaseConfig->getProxyNamespace());
        $entityManagerConfiguration->setAutoGenerateProxyClasses($databaseConfig->getAutogenerateProxyConfiguration());
        // Set the naming strategy
        $entityManagerConfiguration->setNamingStrategy(new UnderscoreNamingStrategy(CASE_LOWER, true));
        // Setup the annotation driver
        $metadataDriver = $entityManagerConfiguration->newDefaultAnnotationDriver(
            $databaseConfig->getModelPaths(),
            false
        );
        $entityManagerConfiguration->setMetadataDriverImpl($metadataDriver);

        $this->entityManager = EntityManager::create(
            $databaseConfig->getConfigArray(),
            $entityManagerConfiguration,
        );
    }

    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    public function getPDO(): Connection
    {
        return $this->getEntityManager()->getConnection();
    }

    abstract public function getConfig(): AbstractDoctrineDBConfig;
}

