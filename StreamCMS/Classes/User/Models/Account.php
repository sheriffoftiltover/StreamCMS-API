<?php

declare(strict_types=1);

namespace StreamCMS\User\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use StreamCMS\Chat\Models\PrivateMessage;
use StreamCMS\Database\StreamCMS\StreamCMSModel;
use StreamCMS\Site\Models\Site;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(uniqueConstraints={
 *         @ORM\UniqueConstraint(name="name_unique", columns={"name"}),
 *         @ORM\UniqueConstraint(name="email_unique", columns={"email"})
 *     })
 */
class Account extends StreamCMSModel
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
     * @ORM\Column(type="string", length=191, nullable=false)
     */
    private string $email;

    /**
     * @var Collection|PrivateMessage[]
     * @ORM\OneToMany(targetEntity="StreamCMS\Chat\Models\PrivateMessage", mappedBy="receiver")
     */
    private $receivedMessages;

    /**
     * @var Collection|PrivateMessage[]
     * @ORM\OneToMany(targetEntity="StreamCMS\Chat\Models\PrivateMessage", mappedBy="sender")
     */
    private $sentMessages;

    /**
     * @var Collection|Site[]
     * @ORM\OneToMany(targetEntity="StreamCMS\Site\Models\Site", mappedBy="owner")
     */
    private $sites;

    public function __construct(string $name, string $email, Site|null $site = null)
    {
        $this->name = $name;
        $this->email = $email;

        $this->receivedMessages = new ArrayCollection();
        $this->sentMessages = new ArrayCollection();
        $this->sites = new ArrayCollection();

        // This indicates that we own this
        if ($site !== null) {
            $this->addSite($site);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getReceivedMessages(): Collection
    {
        return $this->receivedMessages;
    }

    public function getSentMessages(): Collection
    {
        return $this->sentMessages;
    }

    public function getSites(): Collection
    {
        return $this->sites;
    }

    public function addSite(Site $site): bool
    {
        if ($this->sites->contains($site)) {
            return false;
        }
        $this->sites[] = $site;
        return true;
    }
}