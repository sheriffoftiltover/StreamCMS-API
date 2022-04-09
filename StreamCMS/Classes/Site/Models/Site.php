<?php

declare(strict_types=1);

namespace StreamCMS\Site\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use StreamCMS\Database\StreamCMS\StreamCMSModel;
use StreamCMS\User\Models\Account;

/**
 * @ORM\Entity
 */
class Site extends StreamCMSModel
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
    private string $host;

    /**
     * @ORM\OneToMany(targetEntity="StreamCMS\User\Models\RoleSitePermission", mappedBy="site")
     */
    private $roleSitePermissions;

    /**
     * @ORM\OneToMany(targetEntity="StreamCMS\Chat\Models\PrivateMessage", mappedBy="site")
     */
    private $privateMessages;

    /**
     * @ORM\OneToMany(targetEntity="StreamCMS\User\Models\Role", mappedBy="site")
     */
    private $roles;

    /**
     * @ORM\ManyToOne(targetEntity="StreamCMS\User\Models\Account", inversedBy="sites")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=false)
     */
    private Account $owner;

    public function __construct(string $host, Account $owner)
    {
        $this->host = strtolower($host);
        $this->owner = $owner;

        $this->roleSitePermissions = new ArrayCollection();
        $this->privateMessages = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getRoleSitePermissions(): Collection
    {
        return $this->roleSitePermissions;
    }

    public function getPrivateMessages(): Collection
    {
        return $this->privateMessages;
    }

    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function getOwner(): Account
    {
        return $this->owner;
    }
}