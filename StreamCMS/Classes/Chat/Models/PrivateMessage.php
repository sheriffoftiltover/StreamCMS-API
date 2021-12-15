<?php

declare(strict_types=1);

namespace StreamCMS\Chat\Models;

use Doctrine\ORM\Mapping as ORM;
use StreamCMS\Chat\Database\ChatDB;
use StreamCMS\Site\Models\Site;
use StreamCMS\User\Models\Account;
use StreamCMS\Utility\Common\Database\Relational\AbstractDoctrineDatabase;
use StreamCMS\Utility\Common\Models\AbstractDoctrineModel;

/**
 * @ORM\Entity
 */
class PrivateMessage extends AbstractDoctrineModel
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int|null $id;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    private string $message;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":0})
     */
    private bool $read;

    /**
     * @ORM\OneToOne(targetEntity="StreamCMS\Site\Models\Site", inversedBy="privateMessages")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", unique=true, onDelete="SET NULL")
     */
    private Site|null $site;

    /**
     * @ORM\ManyToOne(targetEntity="StreamCMS\User\Models\Account", inversedBy="receivedMessages")
     * @ORM\JoinColumn(name="receiver_account_id", referencedColumnName="id")
     */
    private Account|null $receiver;

    /**
     * @ORM\ManyToOne(targetEntity="StreamCMS\User\Models\Account", inversedBy="sentMessages")
     * @ORM\JoinColumn(name="sender_account_id", referencedColumnName="id")
     */
    private Account|null $sender;

    public static function getDatabase(): AbstractDoctrineDatabase
    {
        return ChatDB::get();
    }
}