<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;
use StreamCMS\Site\Factories\SiteFactory;
use StreamCMS\User\Models\Account;

class SiteSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        parent::getDependencies();
        return [
            'AccountSeeder',
        ];
    }

    public function run(): void
    {
        SiteFactory::create('streamcms.dev', Account::getOneBy(['email' => 'sheriffoftiltover@hotmail.com']));
    }
}
