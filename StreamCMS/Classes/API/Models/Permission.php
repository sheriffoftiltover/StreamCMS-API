<?php

declare(strict_types=1);

namespace StreamCMS\API\Models;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Permission
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $path;

    /**
     * @ORM\Column(type="string", length=7, nullable=false)
     */
    private $method;

    /**
     * @ORM\OneToOne(targetEntity="StreamCMS\User\Models\RoleSitePermission", mappedBy="permission")
     */
    private $roleSitePermissions;
}