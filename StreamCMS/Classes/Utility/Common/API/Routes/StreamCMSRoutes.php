<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Common\API\Routes;

use League\Route\Router;
use StreamCMS\User\API\GuestUserRoutes;
use StreamCMS\User\API\UserRoutes;

final class StreamCMSRoutes
{
    public function __construct(Router $router)
    {
        // Add user routes
        new GuestUserRoutes($router);
        new UserRoutes($router);
    }
}
