<?php

declare(strict_types=1);

namespace StreamCMS\Utility\API;

use Exception;
use JetBrains\PhpStorm\Pure;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use StreamCMS\Utility\API\RequestContexts\IdentityContext;
use StreamCMS\Utility\API\RequestContexts\SiteContext;

/*
 * This class wraps ServerRequest
 * This way we can add context to the request for our api classes to utilize such as:
 *  - The site that it is in reference to
 *  - The token that it is using
 *      - The account that is making the request
 *      - The roles on the account
 *      - The permissions that this account has
 */
class StreamCMSRequest implements ServerRequestInterface
{
    protected IdentityContext $identityContext;
    protected SiteContext $siteContext;

    public function __construct(protected ServerRequest $serverRequest)
    {
        $contentTypeHeader = $serverRequest->getHeader('Content-Type')[0] ?? '';
        $contentType = substr($contentTypeHeader, 0, strpos($contentTypeHeader, ';') ?: strlen($contentTypeHeader));
        if ($contentType === 'application/json') {
            $this->serverRequest = $this->serverRequest->withParsedBody(\json_decode($serverRequest->getBody()->getContents() ?: '[]', true, 512, JSON_THROW_ON_ERROR));
        }
    }

    public function getIdentityContext(): IdentityContext
    {
        return $this->identityContext;
    }

    public function setIdentityContext(IdentityContext $identityContext): void
    {
        $this->identityContext = $identityContext;
    }

    public function getSiteContext(): SiteContext
    {
        return $this->siteContext;
    }

    public function setSiteContext(SiteContext $siteContext): void
    {
        $this->siteContext = $siteContext;
    }

    #[Pure]
    public function getProtocolVersion(): string
    {
        return $this->serverRequest->getProtocolVersion();
    }

    public function withProtocolVersion($version): StreamCMSRequest
    {
        return new static($this->serverRequest->withProtocolVersion($version));
    }

    #[Pure]
    public function getHeaders(): array
    {
        return $this->serverRequest->getHeaders();
    }

    public function hasHeader($name): bool
    {
        return $this->serverRequest->hasHeader($name);
    }

    public function getHeader($name): array
    {
        return $this->serverRequest->getHeader($name);
    }

    public function getHeaderLine($name): string
    {
        return $this->serverRequest->getHeaderLine($name);
    }

    public function withHeader($name, $value): static
    {
        return new static($this->serverRequest->withHeader($name, $value));
    }

    public function withAddedHeader($name, $value): static
    {
        return new static($this->serverRequest->withAddedHeader($name, $value));
    }

    public function withoutHeader($name): static
    {
        return new static($this->serverRequest->withoutHeader($name));
    }

    #[Pure]
    public function getBody(): StreamInterface
    {
        return $this->serverRequest->getBody();
    }

    #[Pure]
    public function withBody(StreamInterface $body): static
    {
        return new static($this->serverRequest->withBody($body));
    }

    public function getRequestTarget(): string
    {
        return $this->serverRequest->getRequestTarget();
    }

    /**
     * @throws Exception
     */
    public function withRequestTarget($requestTarget): static
    {
        $serverRequest = $this->serverRequest->withRequestTarget($requestTarget);
        // LMAO this is dumb as fuck
        return new static($serverRequest instanceof ServerRequest ? $serverRequest : throw new Exception('Invalid Type Returned.'));
    }

    #[Pure]
    public function getMethod(): string
    {
        return $this->serverRequest->getMethod();
    }

    /**
     * @throws Exception
     */
    public function withMethod($method): static
    {
        $serverRequest = $this->serverRequest->withMethod($method);
        // LMAO this is dumb as fuck
        return new static($serverRequest instanceof ServerRequest ? $serverRequest : throw new Exception('Invalid Type Returned.'));
    }

    #[Pure]
    public function getUri(): UriInterface
    {
        return $this->serverRequest->getUri();
    }

    /**
     * @throws Exception
     */
    public function withUri(UriInterface $uri, $preserveHost = false): static
    {
        $serverRequest = $this->serverRequest->withUri($uri, $preserveHost);
        // LMAO this is dumb as fuck
        return new static($serverRequest instanceof ServerRequest ? $serverRequest : throw new Exception('Invalid Type Returned.'));
    }

    #[Pure]
    public function getServerParams(): array
    {
        return $this->serverRequest->getServerParams();
    }

    #[Pure]
    public function getCookieParams(): array
    {
        return $this->serverRequest->getCookieParams();
    }

    #[Pure]
    public function withCookieParams(array $cookies): static
    {
        return new static($this->serverRequest->withCookieParams($cookies));
    }

    #[Pure]
    public function getQueryParams(): array
    {
        return $this->serverRequest->getQueryParams();
    }

    #[Pure]
    public function withQueryParams(array $query): static
    {
        return new static($this->serverRequest->withQueryParams($query));
    }

    #[Pure]
    public function getUploadedFiles(): array
    {
        return $this->serverRequest->getUploadedFiles();
    }

    public function withUploadedFiles(array $uploadedFiles): static
    {
        return new static($this->serverRequest->withUploadedFiles($uploadedFiles));
    }

    #[Pure]
    public function getParsedBody(): object|array|null
    {
        return $this->serverRequest->getParsedBody();
    }

    public function withParsedBody($data): static
    {
        return new static($this->serverRequest->withParsedBody($data));
    }

    #[Pure]
    public function getAttributes(): array
    {
        return $this->serverRequest->getAttributes();
    }

    public function getAttribute($name, $default = null)
    {
        return $this->serverRequest->getAttribute($name, $default);
    }

    public function withAttribute($name, $value): static
    {
        return new static($this->serverRequest->withAttribute($name, $value));
    }

    public function withoutAttribute($name): static
    {
        return new static($this->serverRequest->withoutAttribute($name));
    }
}
