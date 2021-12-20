<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Common\API\Middleware\ContextMiddleware;

use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use StreamCMS\Utility\Common\API\Abstractions\Middleware\StreamCMSHeaderMiddleware;
use StreamCMS\Utility\Common\API\RequestContexts\SiteContext;
use StreamCMS\Utility\Common\API\StreamCMSRequest;

class SiteContextMiddleware extends StreamCMSHeaderMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! $request instanceof StreamCMSRequest) {
            // Oops. Not sure how this happened, but this is bad.
            return new Response(null, 500);
        }
        // Construct our site context
        $siteContext = new SiteContext();
        $siteContext->setSite($this->getHeader($request));
        // Set the identity context on the request
        $request->setSiteContext($siteContext);
        return $handler->handle($request);
    }

    public function getHeaderName(): string
    {
        return 'X-STREAM-CMS-SITE';
    }
}
