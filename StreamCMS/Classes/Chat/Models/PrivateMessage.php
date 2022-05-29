<?php

declare(strict_types=1);

namespace StreamCMS\Chat\Models;

use StreamCMS\Database\StreamCMS\StreamCMSModel;
use StreamCMS\Site\Models\Site;
use StreamCMS\User\Models\Account;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PrivateMessage extends StreamCMSModel
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
     * 
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", onDelete="SET NULL")
     * @ORM\ManyToOne(targetEntity="StreamCMS\Site\Models\Site", inversedBy="privateMessages")
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
}