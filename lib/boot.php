<?php
declare(strict_types=1);

use Destiny\Common\Config;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\DBAL\DriverManager;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

// This should be in the server config
ini_set('date.timezone', 'UTC');

$loader = require_once realpath(__DIR__) . '/autoload.php';
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$app = new Destiny\Common\Application();
$app->setLoader($loader);

$log = new Logger($context->log);
$log->pushHandler(new StreamHandler (Config::$a ['log'] ['path'] . $context->log . '.log', Logger::INFO));
$log->pushProcessor(new Monolog\Processor\WebProcessor());
$log->pushProcessor(new Monolog\Processor\MemoryPeakUsageProcessor());
$log->pushProcessor(new Destiny\Common\Log\SessionRequestProcessor());
$app->setLogger($log);

$app->setConnection(DriverManager::getConnection(Config::$a ['db'], new Doctrine\DBAL\Configuration ()));

$redis = new Redis();
$redis->connect(Config::$a ['redis'] ['host'], Config::$a ['redis'] ['port']);
$redis->select(Config::$a ['redis'] ['database']);
$app->setRedis($redis);

$cache = new RedisCache ();
$cache->setRedis($app->getRedis());
$app->setCacheDriver($cache);
