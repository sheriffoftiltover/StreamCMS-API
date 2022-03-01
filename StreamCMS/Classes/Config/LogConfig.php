<?php

declare(strict_types=1);

namespace StreamCMS\Config;

abstract class LogConfig
{
    public const LOG_CHANNEL = 'StreamCMSLog';
    public const LOG_DIRECTORY = '/var/log/streamcms';
}
