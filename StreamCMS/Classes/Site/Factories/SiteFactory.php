<?php

declare(strict_types=1);

namespace StreamCMS\Site\Factories;

use StreamCMS\Site\Models\Site;
use StreamCMS\User\Models\Account;

final class SiteFactory
{
    public static function create(string $host, Account $owner): Site
    {
        // Create the site
        $site = new Site($host, $owner);
        // Create the default roles for the site

    }
}
