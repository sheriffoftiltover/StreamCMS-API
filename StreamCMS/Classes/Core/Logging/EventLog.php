<?php

declare(strict_types=1);

namespace StreamCMS\Core\Logging;

use Monolog\ErrorHandler;
use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\WhatFailureGroupHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use StreamCMS\Config\LogConfig;

class EventLog implements LoggerInterface
{
    private Logger $logger;

    public function __construct(protected string $logChannel)
    {
        $this->logger = new Logger($logChannel);
        $handlers = [];
        $handlers[] = new StreamHandler('php://stdout', 100);
        $handlers[] = (new StreamHandler(LogConfig::LOG_DIRECTORY . '/streamcms.log', 100))->setFormatter(new LogstashFormatter('StreamCMS'));
        $this->logger->pushHandler(new WhatFailureGroupHandler($handlers));
        $handler = new ErrorHandler($this->logger);
        $handler->registerErrorHandler([], false);
        $handler->registerExceptionHandler();
        $handler->registerFatalHandler();
    }

    public function emergency($message, array $context = [])
    {
        $this->logger->emergency($message, $context);
    }

    public function alert($message, array $context = [])
    {
        $this->logger->alert($message, $context);
    }

    public function critical($message, array $context = [])
    {
        $this->logger->critical($message, $context);
    }

    public function error($message, array $context = [])
    {
        $this->logger->error($message, $context);
    }

    public function warning($message, array $context = [])
    {
        $this->logger->warning($message, $context);
    }

    public function notice($message, array $context = [])
    {
        $this->logger->notice($message, $context);
    }

    public function info($message, array $context = [])
    {
        $this->logger->info($message, $context);
    }

    public function debug($message, array $context = [])
    {
        $this->logger->debug($message, $context);
    }

    public function log($level, $message, array $context = [])
    {
        $this->{$level}($message, $context);
    }
}
