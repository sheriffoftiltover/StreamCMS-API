<?php

declare(strict_types=1);

namespace StreamCMS\User\API\Endpoints;

use League\Route\RouteGroup;
use StreamCMS\Core\API\Abstractions\Routes\AbstractRoutes;
use StreamCMS\User\API\Endpoints\Authentication;

final class GuestUserRoutes extends AbstractRoutes
{
    public function addMiddleware(): void
    {
        // No middleware as this is a guest route.
    }

    public function defineRoutes(): void
    {
        $this->router->group(
            '/token/refresh',
            static function(RouteGroup $group): void
            {
                $group->map(...Authentication\CreateRefreshTokenTwitch::getMap());
            },
        );
        $this->router->group(
            '/token/access',
            static function(RouteGroup $group): void
            {
                $group->map(...Authentication\CreateAccessToken::getMap());
            },
        );
    }
}
