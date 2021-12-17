<?php

declare(strict_types=1);

namespace StreamCMS\User\API;

class GetRefreshToken
{
    public function run()
    {
        // Check if token for particular user exists in redis
        // If it does and the ttl is > 10 seconds
        // Return to user
        // Else Create a new token and store for TTL
        // Return token to user
    }
}
