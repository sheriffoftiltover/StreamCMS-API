<?php

declare(strict_types=1);

require '../../StreamCMSInit.php';
dump(['ye'] ?: 'what');

exit;

class RoleManager
{
    private static array $roles = [];
    private static array $userRoles = [];

    public static function addRole(Role $role): void
    {

    }

    public function getUserRoles(User $user, string|null $site): array
    {

    }


}

class User
{
    public function __construct(protected int $id)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPerms(): array
    {

    }

    public function getRoles(string $site): array
    {

    }
}

class Site
{
    public function __construct(protected int $id, protected string $domain)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }
}

class Role
{
    public function __construct(protected string $name, protected Site $site, protected Role|null $inherits = null, protected array $permissions = [])
    {
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
    }

    public function setInherits(Role|null $inherits): void
    {
        $this->inherits = $inherits;
    }

    public function getInherits(): ?Role
    {
        return $this->inherits;
    }
}

// Testing
// Create a few sites
$site1 = new Site(1, 'site1.com');
$site2 = new Site(2, 'site2.com');

// Create default roles
$userRole = new Role('User', $site1);
$modRole = new Role('Moderator', $site1);
$adminRole = new Role('Admin', $site1);