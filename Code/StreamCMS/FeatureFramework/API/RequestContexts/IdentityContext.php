<?php

declare(strict_types=1);

namespace StreamCMS\Core\API\RequestContexts;

use StreamCMS\User\Models\Account;

class IdentityContext
{
    protected Account|null $account;
    protected string|null $token;

    public function __construct()
    {
        $this->account = null;
        $this->token = null;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): void
    {
        $this->account = $account;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }
}
