<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Common\API\Abstractions\Routes;

use League\Route\Router;

abstract class AbstractRoutes
{
    public function __construct(protected Router $router)
    {
        $this->addMiddleware();
        $this->defineRoutes();
    }

    abstract public function addMiddleware(): void;

    abstract public function defineRoutes(): void;
}
