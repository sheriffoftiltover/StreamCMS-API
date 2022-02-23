<?php

declare(strict_types=1);

namespace StreamCMS\Utility\API\Abstractions\Routes;

use StreamCMS\Utility\API\Abstractions\Router\StreamCMSRouter;

abstract class AbstractRoutes
{
    public function __construct(protected StreamCMSRouter $router)
    {
        $this->addMiddleware();
        $this->defineRoutes();
    }

    abstract public function addMiddleware(): void;

    abstract public function defineRoutes(): void;
}