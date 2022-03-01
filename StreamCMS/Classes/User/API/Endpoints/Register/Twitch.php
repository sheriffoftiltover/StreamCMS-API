<?php

declare(strict_types=1);

namespace StreamCMS\User\API\Endpoints\Register;

use StreamCMS\User\API\Views\Register\Twitch\DebugOutputView;
use StreamCMS\Utility\API\Abstractions\BaseAPIEndpoint;

class Twitch extends BaseAPIEndpoint
{
    private string|null $code;
    private string|null $scope;

    public function parse(): void
    {

    }

    public function run(): DebugOutputView|null
    {
        $body = $this->request->getParsedBody();
        $this->code = $body['code'] ?? null;
        $this->scope = $body['scope'] ?? null;
        // Check if token for particular user exists in redis
        // If it does and the ttl is > 10 seconds
        // Return to user
        // Else Create a new token and store for TTL
        // Return token to user
        return new DebugOutputView(['code' => $this->code, 'scope' => $this->scope]);
    }

    public function getPath(): string
    {
        return '/register/twitch';
    }

    public function getMethod(): string
    {
        return 'POST';
    }
}
