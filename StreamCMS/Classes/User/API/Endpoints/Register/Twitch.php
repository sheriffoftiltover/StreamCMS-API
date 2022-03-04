<?php

declare(strict_types=1);

namespace StreamCMS\User\API\Endpoints\Register;

use StreamCMS\API\Abstractions\BaseAPIEndpoint;
use StreamCMS\API\Views\DebugOutputView;
use StreamCMS\Utility\Services\Twitch\TwitchController;

class Twitch extends BaseAPIEndpoint
{
    private TwitchController $twitchController;

    public function parse(): void
    {
        $body = $this->request->getParsedBody();
        $code = $body['code'] ?? null;
        $scope = $body['scope'] ?? null;
        $this->twitchController = new TwitchController();
        $this->twitchController->setTwitchAuth($code, $scope);
    }

    public function run(): DebugOutputView|null
    {
        $this->twitchController->getTwitchUser();
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
