<?php

declare(strict_types=1);

namespace StreamCMS\User\API\Endpoints;

use League\Route\RouteGroup;
use StreamCMS\Core\API\Abstractions\Routes\AbstractRoutes;

class UserRoutes extends AbstractRoutes
{
    public function addMiddleware(): void
    {

    }

    public function defineRoutes(): void
    {
        $this->router->group(
            '/token/refresh',
            function(RouteGroup $group): void
            {
                
            }
        );
    }
}
