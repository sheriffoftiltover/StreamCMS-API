<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;
use StreamCMS\User\Factories\AccountFactory;

class AccountSeeder extends AbstractSeed
{
    public function run(): void
    {
        AccountFactory::create('sheriffoftiltover', 'sheriffoftiltover@hotmail.com');
    }
}
