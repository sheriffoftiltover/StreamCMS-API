<?php

declare(strict_types=1);

namespace StreamCMS\User\Factories;

use StreamCMS\Site\Models\Site;
use StreamCMS\User\Models\Account;

final class AccountFactory
{
    public static function create(string $name, string $email, Site|null $site = null): Account
    {
        $account = new Account($name, $email);
        if ($site !== null) {
            $account->addSite($site);
        }
        $account->save();
        return $account;
    }
}
