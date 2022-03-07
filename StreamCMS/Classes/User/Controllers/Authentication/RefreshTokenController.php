<?php

declare(strict_types=1);

namespace StreamCMS\User\Controllers\Authentication;

use StreamCMS\API\Tokens\IdentityRefreshToken;
use StreamCMS\Site\Models\Site;
use StreamCMS\User\Models\Account;

class RefreshTokenController
{
    public function __construct(protected Account $account, protected Site $site)
    {
    }

    public function getRefreshToken(): string
    {
        // TODO:
        //  Check if token for particular user exists in redis
        //  If it does and the ttl is > 10 seconds
        //  Return to user
        //  Else Create a new token and store for TTL
        //  Return token to user
        return (new IdentityRefreshToken($this->account, $this->site))->create();
    }
}
