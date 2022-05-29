<?php

declare(strict_types=1);

namespace StreamCMS\Core\API\Abstractions\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use StreamCMS\Core\API\StreamCMSRequest;

abstract class StreamCMSHeaderMiddleware implements MiddlewareInterface
{
    abstract public function getHeaderName(): string;

    protected function getHeader(StreamCMSRequest $request): string|null
    {
        return $request->getHeader($this->getHeaderName())[0] ?? null;
    }
}
