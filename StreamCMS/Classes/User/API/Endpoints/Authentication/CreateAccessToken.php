<?php

declare(strict_types=1);

namespace StreamCMS\User\API\Endpoints\Authentication;

use StreamCMS\Core\API\Abstractions\AbstractAPIEndpoint;
use StreamCMS\Core\API\Abstractions\Interfaces\HasBodyInterface;
use StreamCMS\Core\Utility\Services\Twitch\TwitchController;
use StreamCMS\User\API\Tokens\IdentityRefreshToken;
use StreamCMS\User\API\Views\Tokens\RefreshTokenView;
use StreamCMS\User\Controllers\AccountProviders\TwitchAccountProvider;
use StreamCMS\User\Models\Account;

class CreateAccessToken extends AbstractAPIEndpoint implements HasBodyInterface
{
    private TwitchController $twitchController;

    private string|null $code;
    private Account $account;

    public function parseRequest(): void
    {
        $body = $this->request->getParsedBody();
        $this->code = $body['code'] ?? null;
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
        $this->account = (new TwitchAccountProvider($this->twitchController->getTwitchUser()))->getAccount();
    }

    public function run(): RefreshTokenView|null
    {
        return new RefreshTokenView(IdentityRefreshToken::create($this->account));
    }

    public function getPath(): string
    {
        return '/twitch';
    }

    public function getMethod(): string
    {
        return 'POST';
    }
}
