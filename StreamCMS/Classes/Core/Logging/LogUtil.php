<?php

declare(strict_types=1);

namespace StreamCMS\Core\Logging;

use StreamCMS\Config\LogConfig;

final class LogUtil
{
    private static EventLog|null $eventLog = null;

    public static function init(): void
    {
        if (self::$eventLog === null) {
            self::$eventLog = new EventLog(LogConfig::LOG_CHANNEL);
        }
    }

    public static function emergency($message, array $context = []): void
    {
        self::init();
        self::$eventLog->emergency($message, $context);
    }

    public static function alert($message, array $context = []): void
    {
        self::init();
        self::$eventLog->alert($message, $context);
    }

    public static function critical($message, array $context = []): void
    {
        self::init();
        self::$eventLog->critical($message, $context);
    }

    public static function error($message, array $context = []): void
    {
        self::init();
        self::$eventLog->error($message, $context);
    }

    public static function warning($message, array $context = []): void
    {
        self::init();
        self::$eventLog->warning($message, $context);
    }

    public static function notice($message, array $context = []): void
    {
        self::init();
        self::$eventLog->notice($message, $context);
    }

    public static function info($message, array $context = []): void
    {
        self::init();
        self::$eventLog->info($message, $context);
    }

    public static function debug($message, array $context = []): void
    {
        self::init();
        self::$eventLog->debug($message, $context);
    }
}
