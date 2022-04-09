<?php

declare(strict_types=1);

namespace StreamCMS\User\API\Endpoints;

use League\Route\RouteGroup;
use StreamCMS\API\Abstractions\Routes\AbstractRoutes;
use StreamCMS\User\API\Endpoints\Authentication\CreateRefreshToken;
use StreamCMS\User\API\Endpoints\Register;

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
                $group->map(...Register\Twitch::getMap());
            },
        );
    }
}
