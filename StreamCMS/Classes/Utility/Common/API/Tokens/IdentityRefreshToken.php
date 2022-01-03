<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Common\API\Tokens;

use StreamCMS\Site\Models\Site;
use StreamCMS\User\Models\Account;
use StreamCMS\Utility\Common\Security\Tokens\AbstractJWT;

class IdentityRefreshToken extends AbstractJWT
{
    public function __construct(protected Account $account, protected Site $site)
    {
        parent::__construct();
    }

    public function getSecret(): string
    {
        return $_ENV['IDENTITY_REFRESH_TOKEN_SECRET'];
    }

    public function getMaxAge(): int
    {
        return 14 * 86400;
    }

    public function getAllowedSkew(): int
    {
        return 0;
    }

    public function validate(string $token): void
    {
        // TODO: Implement validate() method.
    }

    public function create(): string
    {
        return $this->token->encode([
            'siteId' => $this->site->getId(),
            'accountId' => $this->account->getId(),
        ]);
    }
}
