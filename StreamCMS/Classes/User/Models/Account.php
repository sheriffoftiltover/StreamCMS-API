<?php

declare(strict_types=1);

namespace StreamCMS\User\Models;

use Doctrine\ORM\Mapping as ORM;
use StreamCMS\Chat\Database\ChatDB;
use StreamCMS\Utility\Common\Database\Relational\AbstractDoctrineDatabase;
use StreamCMS\Utility\Common\Models\AbstractDoctrineModel;

/**
 * @ORM\Entity
 */
class Account extends AbstractDoctrineModel
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
     * @ORM\OneToMany(targetEntity="StreamCMS\Chat\Models\PrivateMessage", mappedBy="receiver")
     */
    private $receivedMessages;

    /**
     * @ORM\OneToMany(targetEntity="StreamCMS\Chat\Models\PrivateMessage", mappedBy="sender")
     */
    private $sentMessages;

    /**
     * @ORM\OneToMany(targetEntity="StreamCMS\Site\Models\Site", mappedBy="owner")
     */
    private $sites;

    public static function getDatabase(): AbstractDoctrineDatabase
    {
        return ChatDB::get();
    }
}