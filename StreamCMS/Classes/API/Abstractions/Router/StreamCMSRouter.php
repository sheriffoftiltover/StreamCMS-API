<?php

declare(strict_types=1);

namespace StreamCMS\API\Abstractions\Router;

use League\Route\Router;
use Psr\Http\Message\ServerRequestInterface;

class StreamCMSRouter extends Router
{
    private bool $preppedRoutes = false;

    // This allows us to prep routes multiple times without issue
    protected function prepRoutes(ServerRequestInterface $request): void
    {
        if ($this->preppedRoutes === false) {
            parent::prepRoutes($request);
            $this->preppedRoutes = true;
        }
    }
}
