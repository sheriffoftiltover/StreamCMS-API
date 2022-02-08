<?php

declare(strict_types=1);

namespace StreamCMS\User\API;

use League\Route\RouteGroup;
use StreamCMS\User\API\Endpoints\Authentication\CreateRefreshToken;
use StreamCMS\Utility\Common\API\Abstractions\Routes\AbstractRoutes;

final class GuestUserRoutes extends AbstractRoutes
{
    public function addMiddleware(): void
    {
        // No middleware as this is a guest route.
    }

    public function defineRoutes(): void
    {
        $this->router->group(
            '/account',
            static function(RouteGroup $group): void
            {
                $group->map(...CreateRefreshToken::getMap());
            },
        );
    }
}
