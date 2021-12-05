<?php

namespace Destiny\Commerce;

use DateTime;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use PDO;

class OrdersService extends Service
{

    /**
     * Singleton
     *
     * @var OrdersService
     */
    protected static $instance = null;

    /**
     * Create a new order and item based on subscription
     *
     * @param array $subscriptionType
     * @param int $userId
     * @return array
     */
    public function createSubscriptionOrder(array $subscriptionType, $userId)
    {
        $ordersService = OrdersService::instance();
        $order = [];
        $order ['userId'] = $userId;
        $order ['description'] = $subscriptionType ['tierLabel'];
        $order ['amount'] = $subscriptionType ['amount'];
        $order ['currency'] = Config::$a ['commerce'] ['currency'];
        $order ['orderId'] = $ordersService->addOrder($order);
        return $order;
    }

    /**
     * Get the singleton instance
     *
     * @return OrdersService
     */
    public static function instance()
    {
        return parent::instance();
    }

    /**
     * Add a 'New' order
     *
     * @param array $order
     * @return int
     */
    public function addOrder(array $order)
    {
        $conn = Application::instance()->getConnection();
        $conn->insert('dfl_orders', ['userId' => $order ['userId'], 'amount' => $order ['amount'], 'currency' => $order ['currency'], 'description' => $order ['description'], 'state' => OrderStatus::_NEW, 'createdDate' => Date::getDateTime('NOW')->format('Y-m-d H:i:s')]);
        $order ['orderId'] = $conn->lastInsertId();
        return $order ['orderId'];
    }

    /**
     * Update an existing orders status
     *
     * @param int $id
     * @param string $state
     */
    public function updateOrderState($id, $state)
    {
        $conn = Application::instance()->getConnection();
        $conn->update('dfl_orders', ['state' => $state], ['orderId' => $id]);
    }

    /**
     * Get an order by orderId
     *
     * @param int $orderId
     */
    public function getOrderById($orderId)
    {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('SELECT * FROM dfl_orders WHERE orderId = :orderId LIMIT 0,1');
        $stmt->bindValue('orderId', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Get an order by orderId and userId
     *
     * @param int $orderId
     * @param int $userId
     */
    public function getOrderByIdAndUserId($orderId, $userId)
    {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('SELECT * FROM dfl_orders WHERE orderId = :orderId AND userId = :userId LIMIT 0,1');
        $stmt->bindValue('orderId', $orderId, PDO::PARAM_INT);
        $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Get a list of order items by the userId
     *
     * @param int $userId
     * @param int $limit
     * @param int $start
     */
    public function getOrdersByUserId($userId, $limit = 10, $start = 0, $order = 'ASC')
    {
        if ($order != 'ASC' && $order != 'DESC') {
            $order = 'ASC';
        }
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
            SELECT * FROM dfl_orders WHERE userId = :userId 
            ORDER BY createdDate ' . $order . '
            LIMIT :start,:limit
        ');
        $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
        $stmt->bindValue('start', $start, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get a list of order items by the userId
     *
     * @param int $userId
     * @param int $limit
     * @param int $start
     */
    public function getCompletedOrdersByUserId($userId, $limit = 10, $start = 0, $order = 'ASC')
    {
        if ($order != 'ASC' && $order != 'DESC') {
            $order = 'ASC';
        }
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
            SELECT * FROM dfl_orders WHERE userId = :userId AND state != \'New\'
            ORDER BY createdDate ' . $order . '
            LIMIT :start,:limit
        ');
        $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
        $stmt->bindValue('start', $start, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Insert an ipn record
     *
     * @return void
     */
    public function addIpnRecord($ipn)
    {
        $conn = Application::instance()->getConnection();
        $conn->insert('dfl_orders_ipn', ['ipnTrackId' => $ipn ['ipnTrackId'], 'ipnTransactionId' => $ipn ['ipnTransactionId'], 'ipnTransactionType' => $ipn ['ipnTransactionType'], 'ipnData' => $ipn ['ipnData']]);
    }

    /**
     * This assumes there is only one profile per order
     * - this wont be the case other than when you are in the process of making an order
     *
     * @param int $orderId
     * @todo dirty
     */
    public function getPaymentProfileByOrderId($orderId)
    {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('SELECT * FROM dfl_orders_payment_profiles WHERE orderId = :orderId LIMIT 0,1');
        $stmt->bindValue('orderId', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * This uses the PP paymentProfileId, not the autoincrement local Id
     *
     * @param int $orderId
     * @todo dirty
     */
    public function getPaymentProfileByPaymentProfileId($paymentProfileId)
    {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('SELECT * FROM dfl_orders_payment_profiles WHERE paymentProfileId = :paymentProfileId LIMIT 0,1');
        $stmt->bindValue('paymentProfileId', $paymentProfileId, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * This uses the PP paymentProfileId, not the autoincrement local Id
     *
     * @param int $orderId
     * @todo dirty
     */
    public function getPaymentProfileById($profileId)
    {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('SELECT * FROM dfl_orders_payment_profiles WHERE profileId = :profileId LIMIT 0,1');
        $stmt->bindValue('profileId', $profileId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Update a payment profile next payment date
     *
     * @param int $paymentProfileId
     * @param DateTime $billingNextDate
     */
    public function updatePaymentProfileNextPayment($paymentProfileId, DateTime $billingNextDate)
    {
        $conn = Application::instance()->getConnection();
        $conn->update('dfl_orders_payment_profiles', ['billingNextDate' => $billingNextDate->format('Y-m-d H:i:s')], ['profileId' => $paymentProfileId]);
    }

    /**
     * Set a payment profile state to cancelled
     *
     * @param int $paymentProfile
     * @param string $state
     */
    public function updatePaymentProfileState($paymentProfileId, $state)
    {
        $conn = Application::instance()->getConnection();
        $conn->update('dfl_orders_payment_profiles', ['state' => $state], ['profileId' => $paymentProfileId]);
    }

    /**
     * Set the paymentProfileId, and state to "Active"
     *
     * @param int $profileId
     * @param int $paymentProfileId
     * @param string $status
     * @todo dirty
     */
    public function updatePaymentProfileId($profileId, $paymentProfileId, $state)
    {
        $conn = Application::instance()->getConnection();
        $conn->update('dfl_orders_payment_profiles', ['paymentProfileId' => $paymentProfileId, 'state' => $state], ['profileId' => $profileId]);
    }

    /**
     * Get a payment by the transaction Id
     *
     * @param string $transactionId
     */
    public function getPaymentByTransactionId($transactionId)
    {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('SELECT * FROM dfl_orders_payments WHERE transactionId = :transactionId LIMIT 0,1');
        $stmt->bindValue('transactionId', $transactionId, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Get a payments order
     *
     * @param int $paymentId
     * @return array
     */
    public function getOrderByPaymentId($paymentId)
    {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
            SELECT * FROM dfl_orders AS a
            INNER JOIN dfl_orders_payments AS b ON (b.orderId = a.orderId)
            WHERE b.paymentId = :paymentId
            LIMIT 0,1
        ');
        $stmt->bindValue('paymentId', $paymentId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Get a users payments
     *
     * @param int $userId
     * @param int $limit
     * @param int $start
     */
    public function getPaymentsByUser($userId, $limit = 10, $start = 0)
    {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
            SELECT payments.* FROM dfl_orders_payments AS `payments`
            INNER JOIN dfl_orders AS `orders` ON (orders.orderId = payments.orderId)
            WHERE orders.userId = :userId
            ORDER BY payments.paymentDate DESC
            LIMIT :start,:limit
        ');
        $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
        $stmt->bindValue('start', $start, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Return payments by orderId
     *
     * @param int $orderId
     * @param int $limit
     * @param int $start
     * @todo this returns payments in ASC order, the getPaymentsByUser returns them in DESC order
     *
     */
    public function getPaymentsByOrderId($orderId, $limit = 100, $start = 0, $order = 'ASC')
    {
        $order = ($order != 'ASC' && $order != 'DESC') ? 'ASC' : $order;
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
            SELECT payments.* FROM dfl_orders_payments AS `payments`
            INNER JOIN dfl_orders AS `orders` ON (orders.orderId = payments.orderId)
            WHERE orders.orderId = :orderId
            ORDER BY payments.paymentDate ' . $order . '
            LIMIT :start,:limit
        ');
        $stmt->bindValue('orderId', $orderId, PDO::PARAM_INT);
        $stmt->bindValue('start', $start, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Return a payment by paymentId
     *
     * @param int $paymentId
     * @return array
     */
    public function getPaymentById($paymentId)
    {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
            SELECT payments.* FROM dfl_orders_payments AS `payments`
            WHERE payments.paymentId = :paymentId
            LIMIT 0,1
        ');
        $stmt->bindValue('paymentId', $paymentId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Add an order payment
     *
     * @param array $payment
     * @return int paymentId
     */
    public function addOrderPayment(array $payment)
    {
        $conn = Application::instance()->getConnection();
        $conn->insert('dfl_orders_payments', ['orderId' => $payment ['orderId'], 'amount' => $payment ['amount'], 'currency' => $payment ['currency'], 'transactionId' => $payment ['transactionId'], 'transactionType' => $payment ['transactionType'], 'paymentType' => $payment ['paymentType'], 'payerId' => $payment ['payerId'], 'paymentStatus' => $payment ['paymentStatus'], 'paymentDate' => $payment ['paymentDate'], 'createdDate' => Date::getDateTime('NOW')->format('Y-m-d H:i:s')]);
        return $conn->lastInsertId();
    }

    /**
     * Update an existing payments status
     *
     * @param number $paymentId
     * @param string $state
     */
    public function updatePaymentStatus($paymentId, $state)
    {
        $conn = Application::instance()->getConnection();
        $conn->update('dfl_orders_payments', ['paymentStatus' => $state], ['paymentId' => $paymentId]);
    }

    /**
     * Returns an easier way to read a billing cycle
     *
     * @param int $frequency
     * @param string $period
     * @return string
     */
    public function buildBillingCycleString($frequency, $period)
    {
        if ($frequency < 1) {
            return 'Never';
        }
        if ($frequency == 1) {
            return 'Once a ' . strtolower($period);
        }
        if ($frequency > 1) {
            return 'Every ' . $frequency . ' ' . strtolower($period) . 's';
        }
        return '';
    }

    /**
     * Create a new payment
     *
     * @param unknown $userId
     * @param array $order
     * @param array $subscriptionType
     * @param DateTime $billingStartDate
     * @return array
     */
    public function createPaymentProfile($userId, array $order, array $subscriptionType, DateTime $billingStartDate)
    {
        $ordersService = OrdersService::instance();
        $paymentProfile = [];
        $paymentProfile ['paymentProfileId'] = '';
        $paymentProfile ['userId'] = $userId;
        $paymentProfile ['orderId'] = $order ['orderId'];
        $paymentProfile ['amount'] = $order ['amount'];
        $paymentProfile ['currency'] = $order ['currency'];
        $paymentProfile ['billingFrequency'] = $subscriptionType ['billingFrequency'];
        $paymentProfile ['billingPeriod'] = $subscriptionType ['billingPeriod'];
        $paymentProfile ['billingStartDate'] = $billingStartDate->format('Y-m-d H:i:s');
        $paymentProfile ['billingNextDate'] = $billingStartDate->format('Y-m-d H:i:s');
        $paymentProfile ['state'] = PaymentStatus::_NEW;
        $paymentProfile ['profileId'] = $ordersService->addPaymentProfile($paymentProfile);
        return $paymentProfile;
    }

    /**
     * Add a recurring payment profile
     *
     * @param array $profile
     * @return int
     */
    public function addPaymentProfile(array $profile)
    {
        $conn = Application::instance()->getConnection();
        $conn->insert('dfl_orders_payment_profiles', ['userId' => $profile ['userId'], 'orderId' => $profile ['orderId'], 'paymentProfileId' => $profile ['paymentProfileId'], 'state' => $profile ['state'], 'amount' => $profile ['amount'], 'currency' => $profile ['currency'], 'billingFrequency' => $profile ['billingFrequency'], 'billingPeriod' => $profile ['billingPeriod'], 'billingStartDate' => $profile ['billingStartDate'], 'billingNextDate' => $profile ['billingNextDate']]);
        return $conn->lastInsertId();
    }

}