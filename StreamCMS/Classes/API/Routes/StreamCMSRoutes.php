<?php

declare(strict_types=1);

namespace StreamCMS\API\Routes;

use StreamCMS\Core\API\Abstractions\Router\StreamCMSRouter;
use StreamCMS\User\API\Endpoints\GuestUserRoutes;
use StreamCMS\User\API\Endpoints\UserRoutes;

final class StreamCMSRoutes
{
    public function __construct(StreamCMSRouter $router)
    {
        // Add user routes
        new GuestUserRoutes($router);
        new UserRoutes($router);
    }
}
