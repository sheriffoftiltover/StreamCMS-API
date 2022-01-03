<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;
use StreamCMS\Database\StreamCMS\StreamCMSDB;
use StreamCMS\User\Models\Account;

class AccountSeeder extends AbstractSeed
{
    public function run(): void
    {
        $account = new Account('sheriffoftiltover', 'sheriffoftiltover@hotmail.com');
        $account->save();
    }
}
