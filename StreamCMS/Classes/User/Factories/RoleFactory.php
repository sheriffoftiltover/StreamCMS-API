<?php

declare(strict_types=1);

namespace StreamCMS\User\Factories;

use StreamCMS\Site\Models\Site;
use StreamCMS\User\Models\Role;

final class RoleFactory
{
    public static function create(string $name, Site $site): Role
    {
        return (new Role($name, $site))->save();
    }
}
