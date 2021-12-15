<?php

declare(strict_types=1);

namespace StreamCMS\Site\Models;

use Doctrine\ORM\Mapping as ORM;
use StreamCMS\Chat\Database\ChatDB;
use StreamCMS\User\Models\Account;
use StreamCMS\Utility\Common\Database\Relational\AbstractDoctrineDatabase;
use StreamCMS\Utility\Common\Models\AbstractDoctrineModel;

/**
 * @ORM\Entity
 */
class Site extends AbstractDoctrineModel
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
     * @ORM\OneToOne(targetEntity="StreamCMS\Chat\Models\PrivateMessage", mappedBy="site")
     */
    private $privateMessages;

    /**
     * @ORM\ManyToOne(targetEntity="StreamCMS\User\Models\Account", inversedBy="sites")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     */
    private Account $owner;

    public static function getDatabase(): AbstractDoctrineDatabase
    {
        return ChatDB::get();
    }
}