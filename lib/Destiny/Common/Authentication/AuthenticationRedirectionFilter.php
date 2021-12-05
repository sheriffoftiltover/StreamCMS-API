<?php
declare(strict_types=1);

namespace Destiny\Common\Authentication;

use Destiny\Common\Application;
use Destiny\Common\Exception;
use Destiny\Common\Session;
use Destiny\Common\User\UserRole;

class AuthenticationRedirectionFilter
{

    public function execute(AuthenticationCredentials $authCreds)
    {
        $authService = AuthenticationService::instance();

        // Make sure the creds are valid
        if (!$authCreds->isValid()) {
            Application::instance()->getLogger()->error(sprintf('Error validating auth credentials %s', var_export($authCreds, true)));
            throw new Exception ('Invalid auth credentials');
        }

        // Account merge
        if (Session::set('accountMerge') === '1') {
            // Must be logged in to do a merge
            if (!Session::hasRole(UserRole::USER)) {
                throw new Exception ('Authentication required for account merge');
            }
            $authService->handleAuthAndMerge($authCreds);
            return 'redirect: /profile/authentication';
        }

        // Follow url *notice the set, returning and clearing the var
        $follow = Session::set('follow');

        // If the user profile doesnt exist, go to the register page
        if (!$authService->getUserAuthProfileExists($authCreds)) {
            Session::set('authSession', $authCreds);
            $url = '/register?code=' . urlencode($authCreds->getAuthCode());
            if (!empty($follow)) {
                $url .= '&follow=' . urlencode($follow);
            }
            return 'redirect: ' . $url;
        }

        // User exists, handle the auth
        $authService->handleAuthCredentials($authCreds);

        if (!empty ($follow) && substr($follow, 0, 1) == '/') {
            return 'redirect: ' . $follow;
        }
        return 'redirect: /profile';

    }
}