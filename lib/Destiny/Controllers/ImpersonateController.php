<?php
declare(strict_types=1);

namespace Destiny\Controllers;

use Destiny\Chat\ChatIntegrationService;
use Destiny\Common\Application;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Session;
use Destiny\Common\User\UserService;
use Destiny\Common\ViewModel;

/**
 * @Controller
 */
class ImpersonateController
{

    /**
     * @Route ("/impersonate")
     * @HttpMethod ({"GET"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     */
    public function impersonate(array $params, ViewModel $model)
    {
        $app = Application::instance();
        if (!Config::$a ['allowImpersonation']) {
            throw new Exception ('Impersonating is not allowed');
        }
        $userId = (isset ($params ['userId']) && !empty ($params ['userId'])) ? $params ['userId'] : '';
        $username = (isset ($params ['username']) && !empty ($params ['username'])) ? $params ['username'] : '';
        if (empty ($userId) && empty ($username)) {
            throw new Exception ('[username] or [userId] required');
        }
        $authService = AuthenticationService::instance();
        $userService = UserService::instance();
        if (!empty ($userId)) {
            $user = $userService->getUserById($userId);
        } elseif ($username !== []) {
            $user = $userService->getUserByUsername($username);
        }

        if (empty ($user)) {
            throw new Exception ('User not found. Try a different userId or username');
        }

        $credentials = $authService->getUserCredentials($user, 'impersonating');
        Session::start(Session::START_NOCOOKIE);
        Session::updateCredentials($credentials);
        ChatIntegrationService::instance()->setChatSession($credentials, Session::getSessionId());
        return 'redirect: /';
    }

}
