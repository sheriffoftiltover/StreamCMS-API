<?php

declare(strict_types=1);

namespace StreamCMS\User\Models;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Role
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=191, nullable=false)
     */
    private $name;

    /**
     * @ORM\Column(type="json", nullable=false)
     */
    private $metadata;

    /**
     * @ORM\OneToOne(targetEntity="StreamCMS\User\Models\RoleSitePermission", mappedBy="role")
     */
    private $roleSitePermissions;

    /**
     * @ORM\ManyToOne(targetEntity="StreamCMS\Site\Models\Site", inversedBy="roles")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", nullable=false)
     */
    private $site;
}