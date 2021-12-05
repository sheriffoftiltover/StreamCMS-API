<?php
declare(strict_types=1);

namespace Destiny\Common\Authentication;

use DateTime;
use Destiny\Chat\ChatIntegrationService;
use Destiny\Common\Application;
use Destiny\Common\Service;
use Destiny\Common\Session;
use Destiny\Common\User\UserService;
use PDO;

class RememberMeService extends Service
{

    /**
     * Singleton
     *
     * @var RememberMeService
     */
    protected static $instance = null;

    /**
     * Get the singleton instance
     *
     * @return RememberMeService
     */
    public static function instance()
    {
        return parent::instance();
    }

    /**
     * Checks the users current session status
     * Does a remember me login
     * @return void
     */
    public function init()
    {
        $app = Application::instance();
        $authService = AuthenticationService::instance();
        // If the session hasnt started, or the data is not valid (result from php clearing the session data), check the Remember me cookie
        if (!Session::isStarted() || !Session::getCredentials()->isValid()) {
            $userId = $authService->getRememberMe();
            if ($userId !== false) {
                $userManager = UserService::instance();
                $user = $userManager->getUserById($userId);
                if (!empty ($user)) {
                    Session::start(Session::START_NOCOOKIE);
                    $credentials = $authService->getUserCredentials($user, 'rememberme');
                    Session::updateCredentials($credentials);
                    ChatIntegrationService::instance()->setChatSession($credentials, Session::getSessionId());
                    $authService->setRememberMe($user);
                }
            }
        }
    }

    /**
     * Clear expired rememberme's
     *
     * @return void
     */
    public function clearExpiredRememberMe()
    {
        $conn = Application::instance()->getConnection();
        $conn->executeQuery('DELETE FROM dfl_users_rememberme WHERE expireDate <= NOW()');
    }

    /**
     * Delete remember me
     *
     * @param int $userId
     * @param string $token
     * @param string $tokenType
     */
    public function deleteRememberMe($userId, $token, $tokenType)
    {
        $conn = Application::instance()->getConnection();
        $conn->delete('dfl_users_rememberme', ['userId' => $userId, 'token' => $token, 'tokenType' => $tokenType]);
    }

    /**
     * Add remember me token
     *
     * @param int $userId
     * @param string $token
     * @param string $tokenType
     * @param DateTime $expire
     * @param DateTime $createdDate
     */
    public function addRememberMe($userId, $token, $tokenType, DateTime $expire, DateTime $createdDate)
    {
        $conn = Application::instance()->getConnection();
        $conn->insert('dfl_users_rememberme', ['userId' => $userId, 'token' => $token, 'tokenType' => $tokenType, 'createdDate' => $createdDate->format('Y-m-d H:i:s'), 'expireDate' => $expire->format('Y-m-d H:i:s')], [PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR]);
    }

    /**
     * Get the user Id of a none expired token
     *
     * @param string $token
     * @param string $tokenType
     * @return array
     */
    public function getRememberMe($token, $tokenType)
    {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('
            SELECT u.userId,r.createdDate,r.expireDate FROM dfl_users_rememberme AS r 
            INNER JOIN dfl_users AS u ON (u.userId = r.userId)
            WHERE r.token = :token AND r.tokenType = :tokenType AND r.expireDate > NOW() 
            LIMIT 0,1
        ');
        $stmt->bindValue('token', $token, PDO::PARAM_STR);
        $stmt->bindValue('tokenType', $tokenType, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

}