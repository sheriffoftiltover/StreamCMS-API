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
     * @ORM\OneToOne(targetEntity="StreamCMS\Core\API\Models\Permission", inversedBy="roleSitePermissions")
     * @ORM\JoinColumn(name="permission_id", referencedColumnName="id", nullable=false, unique=true)
     */
    private $permission;

    /**
     * 
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="StreamCMS\Site\Models\Site", inversedBy="roleSitePermissions")
     */
    private $site;

    /**
     * @ORM\OneToOne(targetEntity="StreamCMS\User\Models\Role", inversedBy="roleSitePermissions")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id", nullable=false, unique=true)
     */
    private $role;
}