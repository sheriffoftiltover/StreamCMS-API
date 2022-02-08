<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Common\API\Abstractions\Routes;

use StreamCMS\Utility\Common\API\Abstractions\Router\StreamCMSRouter;

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
