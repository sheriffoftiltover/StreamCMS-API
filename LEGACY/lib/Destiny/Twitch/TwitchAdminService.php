<?php
declare(strict_types=1);

namespace Destiny\Twitch;

use Destiny\Common\Authentication\AbstractAuthService;
use Destiny\Common\Authentication\AuthProvider;

/**
 * @method static TwitchAdminService instance()
 */
class TwitchAdminService extends AbstractAuthService {

    public $provider = AuthProvider::TWITCHBROADCAST;

    function afterConstruct() {
        parent::afterConstruct();
        $this->authHandler = TwitchBroadcastAuthHandler::instance();
    }

}