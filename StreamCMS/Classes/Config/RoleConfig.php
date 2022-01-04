<?php

declare(strict_types=1);

namespace StreamCMS\Config;

abstract class RoleConfig
{
    public const DEFAULT_ROLES = [
        'Owner',
        'Admin',
        'User',
    ];
}
