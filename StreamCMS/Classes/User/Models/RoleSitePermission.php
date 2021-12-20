<?php

declare(strict_types=1);

namespace StreamCMS\User\Models;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class RoleSitePermission
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="StreamCMS\User\Models\Permission", inversedBy="roleSitePermissions")
     * @ORM\JoinColumn(name="permission_id", referencedColumnName="id", nullable=false, unique=true)
     */
    private $permission;

    /**
     * @ORM\OneToOne(targetEntity="StreamCMS\Site\Models\Site", inversedBy="roleSitePermissions")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", nullable=false, unique=true)
     */
    private $site;

    /**
     * @ORM\OneToOne(targetEntity="StreamCMS\User\Models\Role", inversedBy="roleSitePermissions")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id", nullable=false, unique=true)
     */
    private $role;
}