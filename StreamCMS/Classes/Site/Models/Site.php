<?php

declare(strict_types=1);

namespace StreamCMS\Site\Models;

use Doctrine\ORM\Mapping as ORM;
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
     * @ORM\OneToOne(targetEntity="StreamCMS\User\Models\RoleSitePermission", mappedBy="site")
     */
    private $roleSitePermissions;

    /**
     * @ORM\OneToOne(targetEntity="StreamCMS\Chat\Models\PrivateMessage", mappedBy="site")
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
        $this->host = $host;
        $this->owner = $owner;
    }
}