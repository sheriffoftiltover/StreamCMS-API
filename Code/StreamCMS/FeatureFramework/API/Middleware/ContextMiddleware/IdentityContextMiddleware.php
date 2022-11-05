<?php

declare(strict_types=1);

namespace StreamCMS\Core\API\Middleware\ContextMiddleware;

use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use StreamCMS\Core\API\Abstractions\Middleware\StreamCMSHeaderMiddleware;
use StreamCMS\Core\API\RequestContexts\IdentityContext;
use StreamCMS\Core\API\StreamCMSRequest;

/**
 * Class ExtractIdentityContext
 * @package StreamCMS\Core\API\Middleware
 * Adds identity context to the request
 * Extracts the token from the header if it exists and uses it to update the identity context on the request.
 */
class IdentityContextMiddleware extends StreamCMSHeaderMiddleware
{
    public function process(ServerRequestInterface|StreamCMSRequest $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! $request instanceof StreamCMSRequest) {
            // Oops. Not sure how this happened, but this is bad.
            return new Response(null, 500);
        }
        // Construct our Identity context
        $identityContext = new IdentityContext();
        // FIXME @sheriffoftiltover Try to extract a token from the header
        //  For now just manually create the thing
        $identityContext->setToken('Test Token!');
        // Set the identity context on the request
        $request->setIdentityContext($identityContext);
        return $handler->handle($request);
    }

    public function getHeaderName(): string
    {
        return 'X-STREAM-CMS-TOKEN';
    }
}
