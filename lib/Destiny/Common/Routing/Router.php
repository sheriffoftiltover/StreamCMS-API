<?php
declare(strict_types=1);

namespace Destiny\Common\Routing;

use Destiny\Common\Request;

class Router
{

    /**
     * Setup the router
     * @param array $routes
     */
    public function __construct(array $routes = null)
    {
        if (!empty ($routes)) {
            $this->setRoutes($routes);
        }
    }

    /**
     * Set the route collection
     * @param array<Route> $routes
     */
    public function setRoutes(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Get the route collection
     * @return array<Route>
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Add a route
     * @param Route $route
     */
    public function addRoute(Route $route)
    {
        $this->routes[] = $route;
    }

    /**
     * Find a route
     *
     * @return Route
     */
    public function findRoute(Request $request)
    {
        $path = $request->path();
        $method = $request->method();
        for ($i = 0; $i < count($this->routes); ++$i) {
            if ($this->routes [$i]->testPath($path, $method)) {
                return $this->routes [$i];
            }
        }
        return null;
    }

}

