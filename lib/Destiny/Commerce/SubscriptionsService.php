<?php

namespace Destiny\Commerce;

use DateTime;
use Destiny\Common\Application;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Service;
use PDO;

class SubscriptionsService extends Service
{

    protected static $instance = null;

    /**
     * Singleton
     *
     * @return SubscriptionsService
     */
    public static function instance()
    {
        return parent::instance();
    }

    /**
     * Expires subscritions based on their end date
     *
     * @return int the number of expired subscriptions
     */
    public function expiredSubscriptions()
    {
        $conn = Application::instance()->getConnection();
        $authenticationService = AuthenticationService::instance();

        // Expire recurring subs with a 24 hour grace period
        $stmt = $conn->prepare('SELECT subscriptionId,userId FROM dfl_users_subscriptions WHERE recurring = 1 AND status = :status AND endDate + INTERVAL 24 HOUR <= NOW()');
        $stmt->bindValue('status', SubscriptionStatus::ACTIVE, PDO::PARAM_STR);
        $stmt->execute();
        $subscriptions = $stmt->fetchAll();
        if (!empty ($subscriptions)) {
            foreach ($subscriptions as $sub) {
                $authenticationService->flagUserForUpdate($sub ['userId']);
                $conn->update('dfl_users_subscriptions', ['status' => SubscriptionStatus::EXPIRED], ['subscriptionId' => $sub ['subscriptionId']]);
            }
        }

        // Expire NONE recurring subs immediately
        $stmt = $conn->prepare('SELECT subscriptionId,userId FROM dfl_users_subscriptions WHERE recurring = 0 AND status = :status AND endDate <= NOW()');
        $stmt->bindValue('status', SubscriptionStatus::ACTIVE, PDO::PARAM_STR);
        $stmt->execute();
        $subscriptions = $stmt->fetchAll();
        if (!empty ($subscriptions)) {
            foreach ($subscriptions as $sub) {
                $authenticationService->flagUserForUpdate($sub ['userId']);
                $conn->update('dfl_users_subscriptions', ['status' => SubscriptionStatus::EXPIRED], ['subscriptionId' => $sub ['subscriptionId']]);
            }
        }
    }

    /**
     * Get the first subscription
     * Note: This does not take into account end date.
     * It relies on the subscription status Active
     *
     * @param int $userId
     * @return array
     */
    public function getUserSubscription($userId)
    {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('SELECT * FROM dfl_users_subscriptions WHERE userId = :userId ORDER BY createdDate DESC LIMIT 0,1');
        $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Get the first pending subscription
     *
     * @param int $userId
     * @return array
     */
    public function getUserPendingSubscription($userId)
    {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('SELECT * FROM dfl_users_subscriptions WHERE userId = :userId AND status = :status ORDER BY createdDate DESC LIMIT 0,1');
        $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
        $stmt->bindValue('status', SubscriptionStatus::PENDING, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Update a subscriptions end date
     *
     * @param int $subscriptionId
     * @param DateTime $endDate
     */
    public function updateSubscriptionDateEnd($subscriptionId, DateTime $endDate)
    {
        $conn = Application::instance()->getConnection();
        $conn->update('dfl_users_subscriptions', ['endDate' => $endDate->format('Y-m-d H:i:s')], ['subscriptionId' => $subscriptionId]);
    }

    /**
     * Update a subscriptions recurring field
     *
     * @param int $subscriptionId
     * @param DateTime $endDate
     */
    public function updateSubscriptionRecurring($subscriptionId, $recurring)
    {
        $conn = Application::instance()->getConnection();
        $conn->update('dfl_users_subscriptions', ['recurring' => ($recurring) ? 1 : 0], ['subscriptionId' => $subscriptionId], [PDO::PARAM_BOOL, PDO::PARAM_INT]);
    }

    /**
     * Update a subscriptions status
     *
     * @param int $subscriptionId
     * @param DateTime $endDate
     */
    public function updateSubscriptionState($subscriptionId, $status)
    {
        $conn = Application::instance()->getConnection();
        $conn->update('dfl_users_subscriptions', ['status' => $status], ['subscriptionId' => $subscriptionId], [PDO::PARAM_STR, PDO::PARAM_INT]);
    }

    /**
     * Update a subscriptions payment profile
     *
     * @param int $subscriptionId
     * @param int $profileId
     * @param bool $recurring
     * @return int
     */
    public function updateSubscriptionPaymentProfile($subscriptionId, $profileId, $recurring)
    {
        $conn = Application::instance()->getConnection();
        $conn->update('dfl_users_subscriptions', ['paymentProfileId' => $profileId, 'recurring' => $recurring], ['subscriptionId' => $subscriptionId], [PDO::PARAM_STR, PDO::PARAM_BOOL, PDO::PARAM_INT]);
    }

    /**
     * Get a list of subscriptions by tier
     *
     * @param tinyint $tier
     * @return array<array>
     */
    public function getSubscriptionsByTier($tier)
    {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
          SELECT u.userId,u.username,u.email,s.subscriptionType,s.createdDate,s.endDate,s.subscriptionSource,s.recurring,s.status 
          FROM dfl_users_subscriptions AS s
          INNER JOIN dfl_users AS u ON (u.userId = s.userId)
          WHERE s.subscriptionTier = :subscriptionTier AND s.status = :subscriptionStatus AND s.subscriptionSource = :subscriptionSource
          ORDER BY s.createdDate ASC
        ');
        $stmt->bindValue('subscriptionTier', $tier, PDO::PARAM_INT);
        $stmt->bindValue('subscriptionStatus', SubscriptionStatus::ACTIVE, PDO::PARAM_STR);
        $stmt->bindValue('subscriptionSource', Config::$a ['subscriptionType'], PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get a list of subscriptions by gifter id (userId)
     *
     * @param int $gifterId
     * @return array<array>
     */
    public function getSubscriptionsByGifterId($gifterId)
    {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
          SELECT s.*,u.username `gifterUsername` FROM dfl_users_subscriptions s
          LEFT JOIN dfl_users u ON (u.userId = s.gifter)
          WHERE s.gifter = :gifter
          ORDER BY createdDate DESC
        ');
        $stmt->bindValue('gifter', $gifterId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get a list of active subscriptions by gifter id (userId)
     *
     * @param int $gifterId
     * @return array<array>
     */
    public function getActiveSubscriptionsByGifterId($gifterId)
    {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
          SELECT s.*,u.username `gifterUsername` FROM dfl_users_subscriptions s
          LEFT JOIN dfl_users u ON (u.userId = s.gifter)
          WHERE s.gifter = :gifter AND s.status = :status
          ORDER BY endDate ASC
        ');
        $stmt->bindValue('gifter', $gifterId, PDO::PARAM_INT);
        $stmt->bindValue('status', SubscriptionStatus::ACTIVE, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get a list of active subscriptions by subscriptionId and gifter id (userId)
     *
     * @param int $subscriptionId
     * @param int $gifterId
     * @return array<array>
     */
    public function getActiveSubscriptionByIdAndGifterId($subscriptionId, $gifterId)
    {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
          SELECT s.*,u.username `gifterUsername` FROM dfl_users_subscriptions s
          LEFT JOIN dfl_users u ON (u.userId = s.gifter)
          WHERE s.gifter = :gifter AND s.subscriptionId = :subscriptionId AND s.status = :status
          ORDER BY createdDate DESC
          LIMIT 0,1
        ');
        $stmt->bindValue('gifter', $gifterId, PDO::PARAM_INT);
        $stmt->bindValue('subscriptionId', $subscriptionId, PDO::PARAM_INT);
        $stmt->bindValue('status', SubscriptionStatus::ACTIVE, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Get a subscription by the order
     *
     * @param number $orderId
     * @return array
     */
    public function getSubscriptionByOrderId($orderId)
    {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
          SELECT * FROM dfl_users_subscriptions WHERE orderId = :orderId 
          ORDER BY createdDate DESC 
          LIMIT 0,1
        ');
        $stmt->bindValue('orderId', $orderId, PDO::PARAM_INT);
        $stmt->execute();

        $subscription = $stmt->fetch();
        if (!empty ($subscription)) {
            $subType = $this->getSubscriptionType($subscription ['subscriptionType']);
            $subscription ['tierLabel'] = $subType ['tierLabel'];
        }

        return $subscription;
    }

    /**
     * Get a subscription type by id
     *
     * @param string $subscriptionId
     * @return array
     * @throws Exception
     */
    public function getSubscriptionType($subscriptionId)
    {
        $subscriptions = Config::$a ['commerce'] ['subscriptions'];
        if (!empty ($subscriptionId) && isset ($subscriptions [$subscriptionId])) {
            return $subscriptions [$subscriptionId];
        }
        throw new Exception (sprintf('Subscription type [%s] not found', $subscriptionId));
    }

    /**
     * Add subscription
     * @param array $subscription
     */
    public function addSubscription(array $subscription)
    {
        $conn = Application::instance()->getConnection();
        $conn->insert('dfl_users_subscriptions', $subscription);
        return $conn->lastInsertId();
    }

    /**
     * Update subscription
     * @param array $subscription
     */
    public function updateSubscription(array $subscription)
    {
        $conn = Application::instance()->getConnection();
        $conn->update('dfl_users_subscriptions', $subscription, ['subscriptionId' => $subscription ['subscriptionId']]);
    }

    /**
     * Get all user subscriptions
     *
     * @param number $userId
     * @return array
     */
    public function getUserSubscriptions($userId, $limit = 100, $start = 0)
    {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
          SELECT * FROM dfl_users_subscriptions 
          WHERE userId = :userId
          AND status != \'New\'
          ORDER BY createdDate DESC LIMIT :start,:limit
        ');
        $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('start', $start, PDO::PARAM_INT);
        $stmt->execute();
        $subscriptions = $stmt->fetchAll();
        for ($i = 0; $i < count($subscriptions); $i++) {
            $subType = $this->getSubscriptionType($subscriptions [$i] ['subscriptionType']);
            $subscriptions [$i] ['tierLabel'] = $subType ['tierLabel'];
        }
        return $subscriptions;
    }

    /**
     * Get a subscription by Id
     *
     * @param number $id
     * @return array
     */
    public function getSubscriptionById($subscriptionId)
    {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
            SELECT * FROM dfl_users_subscriptions 
            WHERE subscriptionId = :subscriptionId
            LIMIT 1
        ');
        $stmt->bindValue('subscriptionId', $subscriptionId, PDO::PARAM_INT);
        $stmt->execute();
        $subscription = $stmt->fetch();
        if (!empty ($subscription)) {
            $subType = $this->getSubscriptionType($subscription ['subscriptionType']);
            $subscription ['tierLabel'] = $subType ['tierLabel'];
        }
        return $subscription;
    }

    /**
     * Check if a user has a active subscription
     *
     * @param number $gifter userId
     * @param number $giftee userId
     * @return bool
     */
    public function getCanUserReceiveGift($gifter, $giftee)
    {

        if ($gifter == $giftee) {
            return false;
        }

        // Make sure the the giftee accepts gifts
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('SELECT userId FROM dfl_users WHERE userId = :userId AND allowGifting = 1');
        $stmt->bindValue('userId', $giftee, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() <= 0) {
            return false;
        }

        // make sure the giftee doesnt have an active subscription
        $subscription = $this->getUserActiveSubscription($giftee);
        if (!empty($subscription)) {
            return false;
        }

        return true;
    }

    /**
     * Get the first active subscription
     * Note: This does not take into account end date.
     * It relies on the subscription status Active
     *
     * @param int $userId
     * @return array
     */
    public function getUserActiveSubscription($userId)
    {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
          SELECT s.*,gifter.username `gifterUsername` FROM dfl_users_subscriptions s
          LEFT JOIN dfl_users gifter ON (gifter.userId = s.gifter)
          WHERE s.userId = :userId AND s.status = :status 
          ORDER BY s.createdDate DESC 
          LIMIT 0,1
        ');
        $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
        $stmt->bindValue('status', SubscriptionStatus::ACTIVE, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }
}