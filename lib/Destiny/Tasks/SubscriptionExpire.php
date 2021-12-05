<?php
declare(strict_types=1);

namespace Destiny\Tasks;

use Destiny\Commerce\SubscriptionsService;
use Destiny\Common\Authentication\RememberMeService;
use Psr\Log\LoggerInterface;

class SubscriptionExpire
{

    public function execute(LoggerInterface $log)
    {
        RememberMeService::instance()->clearExpiredRememberMe();
        $expiredSubscriptionCount = SubscriptionsService::instance()->expiredSubscriptions();
        $log->debug(sprintf('Expired (%s)', $expiredSubscriptionCount));
    }

}