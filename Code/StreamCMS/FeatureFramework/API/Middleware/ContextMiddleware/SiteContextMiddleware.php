<?php

declare(strict_types=1);

namespace StreamCMS\Core\API\Middleware\ContextMiddleware;

use Laminas\Diactoros\Response;
use Logging\LogUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use StreamCMS\Core\API\Abstractions\Middleware\StreamCMSHeaderMiddleware;
use StreamCMS\Core\API\RequestContexts\SiteContext;
use StreamCMS\Core\API\StreamCMSRequest;

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
        LogUtil::info("Site: {$siteContext->getSite()?->getHost()}");
        // Set the identity context on the request
        $request->setSiteContext($siteContext);
        return $handler->handle($request);
    }

    public function getHeaderName(): string
    {
        return 'X-STREAM-CMS-SITE';
    }
}
