<?php

declare(strict_types=1);

namespace StreamCMS\Utility\API\Middleware\ResponseMiddleware;

use League\Route\Http\Exception;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use StreamCMS\Utility\Environment\Env;

class ThrowableMiddleware implements MiddlewareInterface
{
    public function __construct(protected ResponseFactoryInterface $responseFactory)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
        } catch (\Throwable $exception) {
            $response = $this->responseFactory->createResponse();
            if ($exception instanceof Exception) {
                $response = $exception->buildJsonResponse($response);
            } elseif (Env::isDev() && Env::isCLI()) {
                throw $exception;
            }
            $responseJson = [
                'statusCode' => 500,
                'errorMessage' => $exception->getMessage(),
            ];
            $response->getBody()->write(json_encode($responseJson, JSON_THROW_ON_ERROR));
            $response = $response->withAddedHeader('Content-Type', 'application/json')->withStatus(500, $exception->getMessage());
        }
        return $response;
    }
}
