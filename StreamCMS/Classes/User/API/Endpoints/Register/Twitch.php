<?php

declare(strict_types=1);

namespace StreamCMS\User\API\Endpoints\Register;

use Laminas\Diactoros\Response;
use StreamCMS\Utility\Common\API\Abstractions\BaseAPIEndpoint;

class Twitch extends BaseAPIEndpoint
{
    private string|null $code;
    private string|null $scope;

    public function parse(): void
    {
        $body = $this->request->getParsedBody();
        $this->code = $body['code'] ?? null;
        $this->scope = $body['scope'] ?? null;
    }

    public function run(): Response|null
    {
        // Check if token for particular user exists in redis
        // If it does and the ttl is > 10 seconds
        // Return to user
        // Else Create a new token and store for TTL
        // Return token to user
        return new Response\JsonResponse(['code' => $this->code, 'scope' => $this->scope]);
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
