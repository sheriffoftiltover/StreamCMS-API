<?php

declare(strict_types=1);

namespace StreamCMS\User\API\Endpoints\Register;

use StreamCMS\API\Abstractions\BaseAPIEndpoint;
use StreamCMS\API\Abstractions\Interfaces\HasBodyInterface;
use StreamCMS\Site\Models\Site;
use StreamCMS\User\API\Views\Tokens\RefreshTokenView;
use StreamCMS\User\Controllers\AccountProviders\TwitchAccountProvider;
use StreamCMS\User\Controllers\Authentication\RefreshTokenController;
use StreamCMS\Utility\Services\Twitch\TwitchController;

class Twitch extends BaseAPIEndpoint implements HasBodyInterface
{
    private TwitchController $twitchController;
    private Site|null $site;
    private string|null $code;

    public function parseRequest(): void
    {
        $body = $this->request->getParsedBody();
        $this->code = $body['code'] ?? null;
        $this->site = $this->request->getSiteContext()->getSite();
    }

    public function validateRequest(): void
    {
        if ($this->site === null) {
            // TODO: Refactor this into a standard way of error handling.
            throw new \Exception('Invalid Site.');
        }
        if ($this->code === null) {
            // TODO: Refactor this into a standard way of error handling.
            throw new \Exception('Invalid Code.');
        }
        $this->twitchController = new TwitchController();
        $this->twitchController->setTwitchAuth($this->code, 'authorization_code');
    }

    public function run(): RefreshTokenView|null
    {
        $account = (new TwitchAccountProvider($this->twitchController->getTwitchUser()))->getAccount();
        $token = (new RefreshTokenController($account, $this->site))->getRefreshToken();
        return new RefreshTokenView($token);
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
