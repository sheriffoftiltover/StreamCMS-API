<?php

declare(strict_types=1);

namespace StreamCMS\Core\API\Strategies;

use JetBrains\PhpStorm\Pure;
use League\Route\Http\Exception\MethodNotAllowedException;
use League\Route\Http\Exception\NotFoundException;
use League\Route\Route;
use League\Route\Strategy\StrategyInterface;
use Logging\LogUtil;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use StreamCMS\Core\API\Middleware\ResponseMiddleware\JsonErrorResponseMiddleware;
use StreamCMS\Core\API\Middleware\ResponseMiddleware\ThrowableMiddleware;
use StreamCMS\Core\API\ResponseFactories\ResponseFactory;
use StreamCMS\Core\API\StreamCMSRequest;
use StreamCMS\Core\Exceptions\API\InvalidRequestInstance;

final class APIStrategy implements StrategyInterface
{
    public function __construct(protected ResponseFactoryInterface $responseFactory)
    {
    }

    public function invokeRouteCallable(Route $route, StreamCMSRequest|ServerRequestInterface $request): ResponseInterface
    {
        // FIXME @sheriffoftiltover We probably shouldn't be throwing exceptions directly in our strategy, but I'll fix it later..
        if (!$request instanceof StreamCMSRequest) {
            throw new InvalidRequestInstance('Must pass StreamCMSRequest into API Strategy.');
        }
        $controller = $route->getCallable();
        $response = $controller($request, $route->getVars(), $route->getPath());
        ResponseFactory::addDefaultHeaders($response);
        LogUtil::debug('invokeRouteCallable');
        return $response;
    }

    // 404
    #[Pure]
    public function getNotFoundDecorator(NotFoundException $exception): MiddlewareInterface
    {
        return new JsonErrorResponseMiddleware($this->responseFactory, $exception);
    }

    // 405
    #[Pure]
    public function getMethodNotAllowedDecorator(MethodNotAllowedException $exception): MiddlewareInterface
    {
        return new JsonErrorResponseMiddleware($this->responseFactory, $exception);
    }

    #[Pure]
    public function getExceptionHandler(): MiddlewareInterface
    {
        return new ThrowableMiddleware($this->responseFactory);
    }

    #[Pure]
    public function getThrowableHandler(): MiddlewareInterface
    {
        return new ThrowableMiddleware($this->responseFactory);
    }
}
