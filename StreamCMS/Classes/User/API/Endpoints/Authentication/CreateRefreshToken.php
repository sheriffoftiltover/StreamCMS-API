<?php

declare(strict_types=1);

namespace StreamCMS\User\API\Endpoints\Authentication;

use StreamCMS\Utility\Common\API\Abstractions\BaseAPIEndpoint;

class CreateRefreshToken extends BaseAPIEndpoint
{
    public function parse(): void
    {
        // TODO: Implement parse() method.
    }

    public function run()
    {
        // Check if token for particular user exists in redis
        // If it does and the ttl is > 10 seconds
        // Return to user
        // Else Create a new token and store for TTL
        // Return token to user
    }

    public function getPath(): string
    {
        return '/token/refresh';
    }

    public function getMethod(): string
    {
        return 'POST';
    }
}
