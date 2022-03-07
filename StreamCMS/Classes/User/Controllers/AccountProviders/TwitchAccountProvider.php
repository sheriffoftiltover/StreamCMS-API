<?php

declare(strict_types=1);

namespace StreamCMS\User\Controllers\AccountProviders;

use StreamCMS\User\Factories\AccountFactory;
use StreamCMS\User\Models\Account;
use StreamCMS\Utility\Services\Twitch\TwitchUser;

class TwitchAccountProvider extends AbstractAccountProvider
{
    private ?Account $account = null;

    public function __construct(protected TwitchUser $twitchUser)
    {
    }

    public function getAccount(): Account
    {
        // Check if an account already exists for this user's email
        $account = Account::findOneBy(['email' => $this->twitchUser->getEmail()]);
        // If we don't have an account, create one.
        if ($account === null) {
            $account = AccountFactory::create($this->twitchUser->getDisplayName(), $this->account->getEmail());
        }
        return $account;
    }
}
