<?php
namespace Destiny\Controllers;

use Destiny\Commerce\OrdersService;
use Destiny\Commerce\PaymentStatus;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Request;
use Destiny\Common\Response;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Http;
use Doctrine\DBAL\DBALException;
use PayPal\IPN\PPIPNMessage;

/**
 * @Controller
 */
class IpnController {

    /**
     * @Route ("/ipn")
     * @ResponseBody
     */
    public function ipn(Request $request, Response $response): string {
        try {
            $body = $request->getBody();
            $ipnMessage = new PPIPNMessage ($body, Config::$a['paypal']['sdk']);
            if (!$ipnMessage->validate()) {
                Log::error('Got a invalid IPN ' . $body, $request->headers);
                $response->setStatus(Http::STATUS_BAD_REQUEST);
                return 'invalid_ipn';
            }

            $data = $ipnMessage->getRawData();
            Log::info('Got a valid IPN [txn_id: {txn_id}, txn_type: {txn_type}]', $data);
            $orderService = OrdersService::instance();
            $orderService->addIpnRecord([
                'ipnTrackId' => $data ['ipn_track_id'],
                'ipnTransactionId' => $data ['txn_id'],
                'ipnTransactionType' => $data ['txn_type'],
                'ipnData' => json_encode($data, JSON_UNESCAPED_UNICODE)
            ]);
            // Handle the IPN
            // TODO should be handled asynchronously
            $this->handleIPNTransaction($data);
            //
        } catch (Exception $e) {
            Log::error("Error handling IPN. {$e->getMessage()}");
        } catch (\Exception $e) {
            Log::critical("Error handling IPN. {$e->getMessage()}");
        }
        return 'ok';
    }

    /**
     * @throws DBALException
     * @throws Exception
     */
    protected function handleIPNTransaction(array $data) {
        $txnId = $data ['txn_id'];
        $txnType = $data ['txn_type'];
        $orderService = OrdersService::instance();
        $subscriptionsService = SubscriptionsService::instance();
        $conn = Application::getDbConn();
        switch (strtoupper($txnType)) {

            // This is sent when a express checkout has been performed by a user
            // We need to handle the case where orders go through, but have pending payments.
            case 'EXPRESS_CHECKOUT' :
                $this->checkTransactionRecipientEmail($data);
                $payment = $orderService->getPaymentByTransactionId($txnId);
                if (!empty ($payment)) {
                    // Make sure the payment values are the same
                    if (number_format($payment ['amount'], 2) != number_format($data ['mc_gross'], 2)) {
                        throw new Exception ('Amount for payment do not match');
                    }
                    try {
                        // Update the payment status and subscription paymentStatus to active (may have been pending)
                        $conn->beginTransaction();
                        $orderService->updatePayment([
                            'paymentId' => $payment ['paymentId'],
                            'paymentStatus' => $data ['payment_status']
                        ]);
                        $subs = $subscriptionsService->getSubscriptionsByPaymentId($payment['paymentId']);

                        if (!empty($subs)) {
                            foreach ($subs as $sub) {
                                $subscriptionsService->updateSubscription([
                                    'subscriptionId' => $sub['subscriptionId'],
                                    'paymentStatus' => PaymentStatus::ACTIVE
                                ]);
                            }
                        } else {
                            Log::info("Payment has no subscriptions {$payment['paymentId']}", $payment);
                        }
                        $conn->commit();
                    } catch (DBALException $e) {
                        $conn->rollBack();
                        throw new Exception('Failed to insert payment record.', $e);
                    }
                } else {
                    Log::warn('Express checkout IPN called, but no payment found {txn_id}', $data);
                }
                break;

            // This is sent from paypal when a recurring payment is billed
            case 'RECURRING_PAYMENT' :
                $this->checkTransactionRecipientEmail($data);
                if (!isset ($data ['payment_status']))
                    throw new Exception ('Invalid payment status');
                if (!isset ($data ['next_payment_date']))
                    throw new Exception ('Invalid next_payment_date');

                $nextPaymentDate = Date::getDateTime($data ['next_payment_date']);
                $subscription = $this->getSubscriptionByPaymentProfileData($data);
                try {
                    $conn->beginTransaction();
                    $subscriptionsService->updateSubscription([
                        'subscriptionId' => $subscription['subscriptionId'],
                        'billingNextDate' => $nextPaymentDate->format('Y-m-d H:i:s'),
                        'paymentStatus' => PaymentStatus::ACTIVE
                    ]);
                    $paymentId = $orderService->addPayment([
                        'payerId' => $data ['payer_id'],
                        'amount' => $data ['mc_gross'],
                        'currency' => $data ['mc_currency'],
                        'transactionId' => $txnId,
                        'transactionType' => $txnType,
                        'paymentType' => $data ['payment_type'],
                        'paymentStatus' => $data ['payment_status'],
                        'paymentDate' => Date::getDateTime($data ['payment_date'])->format('Y-m-d H:i:s'),
                    ]);
                    $orderService->addPurchaseOfSubscription($paymentId, $subscription['subscriptionId']);
                    $conn->commit();
                } catch (DBALException $e) {
                    $conn->rollBack();
                    throw new Exception('Failed to insert payment record.', $e);
                }
                Log::notice('Added order payment {recurring_payment_id} status {profile_status}', $data);
                break;

            case 'RECURRING_PAYMENT_SKIPPED':
                $this->checkTransactionRecipientEmail($data);
                $subscription = $this->findSubscriptionByPaymentProfileData($data);
                if (!empty($subscription)) {
                    $subscriptionsService->updateSubscription([
                        'subscriptionId' => $subscription['subscriptionId'],
                        'paymentStatus' => PaymentStatus::SKIPPED
                    ]);
                    Log::debug('Payment skipped {recurring_payment_id}', $data);
                }
                break;

            case 'RECURRING_PAYMENT_PROFILE_CANCEL' :
                $this->checkTransactionRecipientEmail($data);
                $subscription = $this->findSubscriptionByPaymentProfileData($data);
                if (!empty($subscription)) {
                    $subscriptionsService->updateSubscription([
                        'subscriptionId' => $subscription['subscriptionId'],
                        'paymentStatus' => PaymentStatus::CANCELLED
                    ]);
                    Log::debug('Payment profile cancelled {recurring_payment_id} status {profile_status}', $data);
                }
                break;

            case 'RECURRING_PAYMENT_FAILED' :
                $this->checkTransactionRecipientEmail($data);
                $subscription = $this->findSubscriptionByPaymentProfileData($data);
                if (!empty($subscription)) {
                    $subscriptionsService->updateSubscription([
                        'subscriptionId' => $subscription['subscriptionId'],
                        'paymentStatus' => PaymentStatus::FAILED
                    ]);
                    Log::debug('Payment profile cancelled {recurring_payment_id} status {profile_status}', $data);
                }
                break;

            // Sent on first post-back when the user subscribes
            case 'RECURRING_PAYMENT_PROFILE_CREATED' :
                $this->checkTransactionRecipientEmail($data);
                $subscription = $this->getSubscriptionByPaymentProfileData($data);
                $subscriptionsService->updateSubscription([
                    'subscriptionId' => $subscription['subscriptionId'],
                    'paymentStatus' => PaymentStatus::ACTIVE
                ]);
                Log::debug('Updated payment profile {recurring_payment_id} status {profile_status}', $data);
                break;

            case 'ADJUSTMENT':
                Log::debug('Received payment adjustment'. $data['reason_code'], $data);
                break;
        }
    }

    /**
     * @throws Exception
     */
    protected function getSubscriptionByPaymentProfileData(array $data): array {
        $subscription = null;
        $paymentId = $data['recurring_payment_id'] ?? null;
        if (!empty($paymentId)) {
            $subscriptionService = SubscriptionsService::instance();
            $subscription = $subscriptionService->findByPaymentProfileId($paymentId);
        }
        if (empty($subscription)) {
            throw new Exception("Could not load subscription using IPN [#$paymentId]");
        }
        return $subscription;
    }

    /**
     * @return array|null
     */
    protected function findSubscriptionByPaymentProfileData(array $data) {
        try {
            return $this->getSubscriptionByPaymentProfileData($data);
        } catch (Exception $e) {
            Log::warn($e->getMessage());
        }
        return null;
    }

    /**
     * @throws Exception
     */
    private function checkTransactionRecipientEmail(array $data) {
        $email = $data['receiver_email'] ?? null;
        if (empty($email) || strcasecmp(Config::$a['commerce']['receiver_email'], $email) !== 0) {
            throw new Exception("IPN originated with incorrect receiver_email [$email]");
        }
    }

}