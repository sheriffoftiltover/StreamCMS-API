<?php
namespace Destiny\Chat;

use Destiny\Common\Service;
use Destiny\Common\Application;
use Destiny\Commerce\SubscriptionStatus;

class ChatlogService extends Service {
    
    /**
     * Singleton
     *
     * @var ChatlogService
     */
    protected static $instance = null;

    /**
     * Get the singleton instance
     *
     * @return ChatlogService
     */
    public static function instance() {
        return parent::instance ();
    }

    /**
     * Returns a chat log list
     *
     * @return array
     */
    public function getChatLog($limit) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
            SELECT
                u.username,
                u2.username AS target,
                l.event,
                l.data,
                l.timestamp,
                IF(subs.userId IS NULL,0,1) AS `subscriber`,
                subs.subscriptionType AS `subscriptionType`,
                subs.subscriptionTier AS `subscriptionTier`,
                (
                    SELECT GROUP_CONCAT( DISTINCT fn.featureName)
                    FROM dfl_users_features AS uf
                    INNER JOIN dfl_features AS fn ON (fn.featureId = uf.featureId)
                    WHERE uf.userId = u.userId
                    ORDER BY fn.featureId ASC
                ) AS `features`
            FROM
                chatlog AS l
                LEFT JOIN dfl_users AS u ON u.userId = l.userid
                LEFT JOIN dfl_users AS u2 ON u2.userId = l.targetuserid
                LEFT JOIN dfl_users_subscriptions AS `subs` ON (
                    subs.subscriptionId = (
                        SELECT subs2.subscriptionId
                        FROM dfl_users_subscriptions AS subs2
                        WHERE
                            subs2.userId = u.userId AND
                            subs2.status = :status
                        ORDER BY subs2.subscriptionId DESC
                        LIMIT 1
                    )
                )
            WHERE
                l.event NOT IN("JOIN", "QUIT")
            ORDER BY l.id DESC
            LIMIT 0,:limit
        ' );
        
        $stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
        $stmt->bindValue ( 'status', SubscriptionStatus::ACTIVE, \PDO::PARAM_INT );
        $stmt->execute ();
        return $stmt->fetchAll ();
    }

    /**
     * Get the last X number of messages from a specific user starting at a specific date (going backwards)
     *
     * @param int $userId           
     * @param DateTime $startRange          
     * @param int $limit            
     * @param int $start            
     */
    public function getChatLogBanContext($userId, \DateTime $startRange, $limit = 10, $start = 0) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
            SELECT
                u.username,
                u2.username AS target,
                l.event,
                l.data,
                l.timestamp
            FROM
                chatlog AS l
                LEFT JOIN dfl_users AS u ON u.userId = l.userid
                LEFT JOIN dfl_users AS u2 ON u2.userId = l.targetuserid
            WHERE
                l.event NOT IN("JOIN", "QUIT")
                AND l.timestamp <= :startRange
            ORDER BY l.id DESC
            LIMIT :start,:limit
        ' );
        $stmt->bindValue ( 'startRange', $startRange, \Doctrine\DBAL\Types\DateTimeType::DATETIME );
        $stmt->bindValue ( 'start', $start, \PDO::PARAM_INT );
        $stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
        $stmt->execute ();
        return $stmt->fetchAll ();
    }
    
    /**
     * Get the latest broadcasts
     * 
     * @param DateTime $startRange
     * @param number $limit
     * @param number $start
     */
    public function getBroadcasts(\DateTime $startRange, $limit=1, $start=0){
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
            SELECT
                u.username,
                u2.username AS target,
                l.event,
                l.data,
                l.timestamp
            FROM
                chatlog AS l
                LEFT JOIN dfl_users AS u ON u.userId = l.userid
                LEFT JOIN dfl_users AS u2 ON u2.userId = l.targetuserid
            WHERE
                l.event IN("BROADCAST")
                AND l.timestamp >= :startRange
            ORDER BY l.id DESC
            LIMIT :start,:limit
        ' );
        $stmt->bindValue ( 'startRange', $startRange, \Doctrine\DBAL\Types\DateTimeType::DATETIME );
        $stmt->bindValue ( 'start', $start, \PDO::PARAM_INT );
        $stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
        $stmt->execute ();
        return $stmt->fetchAll ();
    }

}