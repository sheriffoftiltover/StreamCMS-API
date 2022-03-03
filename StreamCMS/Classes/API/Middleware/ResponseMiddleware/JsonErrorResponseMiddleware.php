<?php

declare(strict_types=1);

namespace StreamCMS\API\Middleware\ResponseMiddleware;

use League\Route\Http\Exception;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use StreamCMS\API\ResponseFactories\ResponseFactory;

final class JsonErrorResponseMiddleware implements MiddlewareInterface
{

    public function __construct(private ResponseFactoryInterface $responseFactory, private Exception $exception)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        $headers = $this->exception->getHeaders();
        $headers['Content-Type'] = 'application/json';
        // Add our headers
        foreach ($headers as $key => $val) {
            $response = $response->withAddedHeader($key, $val);
        }

        ResponseFactory::addDefaultHeaders($response);

        // If our body is writable, we should add the body
        if ($response->getBody()->isWritable()) {
            $response->getBody()->write(
                json_encode(
                    [
                        'statusCode' => $this->exception->getStatusCode(),
                        'errorMessage' => $this->exception->getMessage()
                    ],
                    JSON_THROW_ON_ERROR
                )
            );
        }
        return $response->withStatus($this->exception->getStatusCode(), $this->exception->getMessage());
    }
}
