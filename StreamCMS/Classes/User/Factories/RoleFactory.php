<?php

declare(strict_types=1);

namespace StreamCMS\User\Factories;

use StreamCMS\Config\RoleConfig;
use StreamCMS\Site\Models\Site;
use StreamCMS\User\Models\Role;

final class RoleFactory
{
    public static function create(string $name, Site $site): Role
    {
        return (new Role($name, $site))->save();
    }

    public static function createDefaultRoles(Site $site): void
    {
        // Create the default roles
        foreach (RoleConfig::DEFAULT_ROLES as $roleName) {
            self::create($roleName, $site);
            // TODO: Figure out how we're going to handle the permissions...
        }
    }
}
