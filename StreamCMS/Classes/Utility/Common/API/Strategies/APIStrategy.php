<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Common\API\Strategies;

use League\Route\Http\Exception\MethodNotAllowedException;
use League\Route\Http\Exception\NotFoundException;
use League\Route\Route;
use League\Route\Strategy\StrategyInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

final class APIStrategy implements StrategyInterface
{
    public function __construct(protected ResponseFactoryInterface $responseFactory)
    {
    }

    public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface
    {
        $controller = $route->getCallable();

        return $controller($request, $route->getVars(), $route->getPath());
    }

    // 404
    public function getNotFoundDecorator(NotFoundException $exception): MiddlewareInterface
    {
        // TODO: Implement getNotFoundDecorator() method.
    }

    // 405
    public function getMethodNotAllowedDecorator(MethodNotAllowedException $exception): MiddlewareInterface
    {
        // TODO: Implement getMethodNotAllowedDecorator() method.
    }

    public function getExceptionHandler(): MiddlewareInterface
    {
        // TODO: Implement getExceptionHandler() method.
    }


    public function getThrowableHandler(): MiddlewareInterface
    {
        // TODO: Implement getThrowableHandler() method.
    }
}
