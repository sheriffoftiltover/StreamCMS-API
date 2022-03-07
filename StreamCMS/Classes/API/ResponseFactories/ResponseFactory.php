<?php

declare(strict_types=1);

namespace StreamCMS\API\ResponseFactories;

use Psr\Http\Message\ResponseInterface;

class ResponseFactory extends \Laminas\Diactoros\ResponseFactory
{
    /**
     * {@inheritDoc}
     */
    public function createResponse(int $code = 200, string $reasonPhrase = '') : ResponseInterface
    {
        $response = parent::createResponse($code, $reasonPhrase);
        static::addDefaultHeaders($response);
        return $response;
    }

    public static function addDefaultHeaders(ResponseInterface &$response): void
    {
        // Add our default headers.
        foreach (static::getDefaultHeaders() as $headerName => $headerValue) {
            $response = $response->withAddedHeader($headerName, $headerValue);
        }
    }

    /**
     * Returns an array of default headers to add to every response.
     * @return array
     */
    public static function getDefaultHeaders(): array
    {
        // FIXME @sheriffoftiltover Update this eventually
        //  TODO: Add system for handling CORS:
        //      https://stackoverflow.com/questions/32500073/request-header-field-access-control-allow-headers-is-not-allowed-by-itself-in-pr
        //      https://fetch.spec.whatwg.org/#http-cors-protocol
        return [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => [
                'GET',
                'POST',
                'PATCH',
                'PUT',
                'DELETE',
                'OPTIONS',
            ],
            'Access-Control-Allow-Headers' => [
                'Content-Type',
                'X-STREAM-CMS-SITE',
                'X-STREAM-CMS-TOKEN',
                'Transfer-Encoding',
                'Content-Length',
                'User-Agent',
                'Accept',
                'content-type',
            ],
        ];
    }
}
