<?php

declare(strict_types=1);

namespace StreamCMS\User\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use StreamCMS\Database\StreamCMS\StreamCMSModel;
use StreamCMS\Site\Models\Site;

/**
 * @ORM\Entity
 */
class Role extends StreamCMSModel
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int|null $id;

    /**
     * @ORM\Column(type="string", length=191, nullable=false)
     */
    private string $name;

    /**
     * @ORM\Column(type="json", nullable=false)
     */
    private $metadata;

    /**
     * @var Collection|RoleSitePermission[]
     * @ORM\OneToOne(targetEntity="StreamCMS\User\Models\RoleSitePermission", mappedBy="role")
     */
    private $roleSitePermissions;

    /**
     * @ORM\ManyToOne(targetEntity="StreamCMS\Site\Models\Site", inversedBy="roles")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", nullable=false)
     */
    private Site $site;

    public function __construct(string $name, Site $site)
    {
        $this->name = $name;
        $this->site = $site;

        $this->roleSitePermissions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addRoleSitePermission(RoleSitePermission $roleSitePermission): bool
    {
        if ($this->roleSitePermissions->contains($roleSitePermission)) {
            return false;
        }
        $this->roleSitePermissions->add($roleSitePermission);
        return true;
    }

    public function getRoleSitePermissions(): ArrayCollection|Collection|array
    {
        return $this->roleSitePermissions;
    }

    public function getSite(): Site
    {
        return $this->site;
    }
}