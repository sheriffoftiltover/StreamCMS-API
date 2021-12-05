<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatBanService;
use Destiny\Chat\ChatRedisService;
use Destiny\Commerce\DonationService;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Commerce\SubscriptionStatus;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Authentication\DggOAuthService;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Request;
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserAuthService;
use Destiny\Common\User\UserService;
use Destiny\Common\User\UserStatus;
use Destiny\Common\Utils\Country;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\RandomString;
use Destiny\Common\ViewModel;
use Destiny\Discord\DiscordAuthHandler;
use Destiny\Discord\DiscordMessenger;
use Destiny\Google\GoogleAuthHandler;
use Destiny\Google\GoogleRecaptchaHandler;
use Destiny\Reddit\RedditAuthHandler;
use Destiny\Twitch\TwitchAuthHandler;
use Destiny\Twitter\TwitterAuthHandler;
use Destiny\YouTube\YouTubeAuthHandler;
use Destiny\YouTube\YouTubeApiService;
use Destiny\YouTube\YouTubeMembershipService;

/**
 * @Controller
 */
class ProfileController {

    /**
     * @Route ("/profile")
     * @HttpMethod ({"GET"})
     * @Secure ({"USER"})
     * @throws Exception
     */
    public function profile(ViewModel $model): string {
        $userService = UserService::instance();
        $userAuthService = UserAuthService::instance();
        $chatBanService = ChatBanService::instance();
        $subscriptionsService = SubscriptionsService::instance();
        $creds = Session::getCredentials();
        $userId = $creds->getUserId();
        $model->credentials = $creds->getData();
        $model->ban = $chatBanService->getUserActiveBan($userId);
        $model->user = $userService->getUserById($userId);
        $model->gifts = $subscriptionsService->findByGifterIdAndStatus($userId, SubscriptionStatus::ACTIVE);
        $model->discordAuthProfile = $userAuthService->getByUserIdAndProvider($userId, AuthProvider::DISCORD);
        $model->subscriptions = $subscriptionsService->getUserActiveAndPendingSubscriptions($userId);

        $model->youTubeAuthDetails = $userAuthService->getByUserIdAndProvider($userId, AuthProvider::YOUTUBE);
        $model->youTubeMembership = YouTubeMembershipService::instance()->getMembershipDetailsForUserId($userId);

        $model->title = 'Account';
        return 'profile/account';
    }

    /**
     * @Route ("/profile/usernamecheck")
     * @HttpMethod ({"GET"})
     * @Secure ({"USER"})
     * @ResponseBody
     */
    public function checkUsername(array $params): array {
        try {
            FilterParams::declared($params, 'username');
            $username = (string) $params['username'];
            $userId = Session::getCredentials()->getUserId();

            $authService = AuthenticationService::instance();
            $authService->validateUsername($username);
            $authService->checkUsernameForSimilarityToAllEmotes($username);

            UserService::instance()->checkUsernameTaken($username, $userId);
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
        return ['success' => true];
    }

    /**
     * @Route ("/profile/usernamechange")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     */
    public function changeUsername(array $params): string {
        $creds = Session::getCredentials();
        $userId = $creds->getUserId();
        try {
            FilterParams::required($params, 'username');
            $userService = UserService::instance();
            $authService = AuthenticationService::instance();
            $user = $userService->getUserById($userId);
            if (boolval($user['allowNameChange'])) {
                $username = $params['username'];

                try {
                    $userService->checkUsernameTaken($username, $userId);
                    $authService->validateUsername($username);
                    $authService->checkUsernameForSimilarityToAllEmotes($username);
                } catch (Exception $e) {
                    Session::setErrorBag("Username taken... stop cheating. {$e->getMessage()}");
                    Log::warn("Failed to change username $userId. {$e->getMessage()}");
                    return 'redirect: /profile';
                }

                $original = $user['username'];
                $user['username'] = $username;
                $userService->updateUser($userId, [
                    'username' => $username,
                    'allowNameChange' => 0,
                    'allowChatting' => 1 // TODO
                ]);
                $authService->updateWebSession($user, $creds->getAuthProvider());
                if ($original != $username) {
                    DiscordMessenger::send('Username changed', [
                        'fields' => [
                            ['title' => 'User', 'value' => DiscordMessenger::userLink($user['userId'], $user['username']), 'short' => false],
                            ['title' => 'Original', 'value' => DiscordMessenger::userLink($user['userId'], $original), 'short' => false],
                        ]
                    ]);
                    Session::setSuccessBag("Your username is now $username, excellent choice!");
                } else {
                    Session::setSuccessBag("Your username is confirmed, you are welcome!");
                }
            } else {
                Session::setErrorBag("You aren't allowed to change your username.");
            }
        } catch (Exception $e) {
            Session::setErrorBag("Failed to change username. {$e->getMessage()}");
            Log::warn("Failed to change username $userId. {$e->getMessage()}");
        }
        return 'redirect: /profile';
    }

    /**
     * @Route ("/profile/username/similaremotes")
     * @HttpMethod ({"GET"})
     * @Secure ({"USER"})
     * @ResponseBody
     */
    public function emotesTooSimilarToUsername(array $params): array {
        try {
            FilterParams::declared($params, 'username');
            $username = $params['username'];

            $authService = AuthenticationService::instance();
            $emotes = $authService->getEmotesTooSimilarToUsername($username);

            return ['data' => ['emotes' => $emotes]];
        } catch (Exception $e) {
            return ['error' => ['message' => $e->getMessage()]];
        }
    }

    /**
     * @Route ("/profile/update")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     * @throws Exception
     */
    public function profileSave(array $params): string {
        $userService = UserService::instance();
        $authService = AuthenticationService::instance();
        $userId = Session::getCredentials()->getUserId();
        $user = $userService->getUserById($userId);
        if (empty($user)) {
            throw new Exception('Invalid user');
        }
        $email = isset($params['email']) && !empty($params['email']) ? $params['email'] : $user['email'];
        $country = isset($params['country']) && !empty($params['country']) ? $params['country'] : $user['country'];
        $allowGifting = isset($params['allowGifting']) ? $params['allowGifting'] : $user['allowGifting'];
        $userData = [
            'email' => $email,
            'allowGifting' => $allowGifting,
            'country' => $country,
        ];
        $userService->updateUser($user['userId'], $userData);
        $authService->flagUserForUpdate($user['userId']);
        Session::setSuccessBag('Your profile has been updated');
        return 'redirect: /profile';
    }

    /**
     * @Route("/profile/developer")
     * @Secure ({"USER"})
     * @throws Exception
     */
    public function profileDeveloper(ViewModel $model): string {
        $userId = Session::getCredentials()->getUserId();
        $oauthService = DggOAuthService::instance();
        $userService = UserService::instance();
        $model->title = 'Developer';
        $model->user = $userService->getUserById($userId);
        $model->oauthClients = $oauthService->getAuthClientsByUserId($userId);
        $model->accessTokens = $oauthService->getAccessTokensByUserId($userId);
        return 'profile/developer';
    }

    /**
     * @Route ("/profile/authentication")
     * @Secure ({"USER"})
     * @throws Exception
     */
    public function profileAuthentication(ViewModel $model): string {
        $userService = UserService::instance();
        $userAuthService = UserAuthService::instance();
        $userId = Session::getCredentials()->getUserId();
        $authProfiles = $userAuthService->getByUserId($userId);
        $model->title = 'Authentication';
        $model->authProfileTypes = array_map(function($v){ return $v['authProvider']; }, $authProfiles);
        $model->authProfiles = $authProfiles;
        $model->user = $userService->getUserById($userId);
        return 'profile/authentication';
    }

    /**
     * @Route ("/profile/app/secret")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     * @ResponseBody
     */
    public function appSecretUpdate(array $params): array {
        try {
            FilterParams::required($params, 'id');
            $userId = Session::getCredentials()->getUserId();
            $oauthService = DggOAuthService::instance();
            $client = $oauthService->getAuthClientById($params['id']);
            if (empty($client)) {
                throw new Exception('Invalid client_id');
            }
            if ($client['ownerId'] != $userId) {
                throw new Exception('You are not the owner of this client.');
            }
            $clientSecret = RandomString::make(64);
            $oauthService->updateAuthClient($params['id'], ['clientSecret' => hash('sha256', $clientSecret)]);
            return ['secret' => $clientSecret];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * @Route ("/profile/app/create")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     */
    public function appCreate(array $params, Request $request): string {
        try {
            FilterParams::required($params, 'name');
            FilterParams::required($params, 'redirectUrl');

            $userId = Session::getCredentials()->getUserId();
            $googleRecaptchaHandler = new GoogleRecaptchaHandler();
            $googleRecaptchaHandler->resolveWithRequest($request);
            $oauthService = DggOAuthService::instance();

            // Validate the application name
            $name = trim($params['name']);
            $this->validateAppName($name);

            // Validate redirectUrl
            $redirect = trim($params['redirectUrl']);
            $this->validateAppRedirect($redirect);

            // only allow 1 max application
            if (count($oauthService->getAuthClientsByUserId($userId)) >= 1) {
                throw new Exception ('You have reached the maximum [1] allowed applications.');
            }

            $oauthService->addAuthClient([
                'clientCode' => RandomString::makeUrlSafe(32),
                'clientSecret' => RandomString::make(64),
                'redirectUrl' => $redirect,
                'clientName' => $name,
                'ownerId' => $userId
            ]);
            Session::setSuccessBag('Application created!');
        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
        }
        return 'redirect: /profile/developer';
    }

    /**
     * @Route ("/profile/app/update")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     */
    public function appUpdate(array $params): string {
        try {
            FilterParams::required($params, 'id');
            FilterParams::required($params, 'name');
            FilterParams::required($params, 'redirectUrl');

            $userId = Session::getCredentials()->getUserId();
            $oauthService = DggOAuthService::instance();
            $client = $oauthService->getAuthClientById($params['id']);

            if (empty($client)) {
                throw new Exception('Invalid client_id');
            }
            if ($client['ownerId'] != $userId) {
                throw new Exception('You are not the owner of this client.');
            }

            $name = trim($params['name']);
            $this->validateAppName($name);

            $redirect = trim($params['redirectUrl']);
            $this->validateAppRedirect($redirect);

            $oauthService->updateAuthClient($params['id'], ['clientName' => $name, 'redirectUrl' => $redirect]);
            Session::setSuccessBag('Application updated!');
        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
        }
        return 'redirect: /profile/developer';
    }

    /**
     * @Route ("/profile/app/{id}/remove")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     * @throws Exception
     */
    public function appDelete(array $params): string {
        FilterParams::required($params, 'id');
        $userId = Session::getCredentials()->getUserId();
        $oauthService = DggOAuthService::instance();
        $client = $oauthService->getAuthClientById($params['id']);
        if (empty($client)) {
            throw new Exception('Invalid client_id');
        }
        if ($client['ownerId'] != $userId) {
            throw new Exception('You are not the owner of this client.');
        }
        // Remove all associated access tokens
        $accessTokens = $oauthService->getAccessTokensByClientId($params['id']);
        foreach ($accessTokens as $token) {
            $oauthService->removeAccessToken($token['tokenId']);
        }
        // Remove the client
        $oauthService->removeAuthClient($params['id']);
        Session::setSuccessBag('Application removed');
        return 'redirect: /profile/developer';
    }

    /**
     * @Route ("/profile/authtoken/create")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     */
    public function accessTokenCreate(Request $request): string {
        try {
            $googleRecaptchaHandler = new GoogleRecaptchaHandler();
            $googleRecaptchaHandler->resolveWithRequest($request);

            $oauthService = DggOAuthService::instance();
            $userId = Session::getCredentials()->getUserId();

            // Users are only allowed 5 max login keys
            // We deem an access token a "login key" when it has no client
            // Typically it also is none expiring
            $accessTokens = $oauthService->getAccessTokensByUserId($userId);
            if (count(array_filter($accessTokens, function($v){ return $v['clientId'] == 0; })) >= 5) {
                throw new Exception ('You have reached the maximum [5] allowed login keys.');
            }

            $accessToken = RandomString::makeUrlSafe(64);
            $oauthService->addAccessToken([
                'clientId' => null,
                'userId' => $userId,
                'token' => $accessToken,
                'refresh' => null,
                'scope' => 'identify',
                'expireIn' => null,
            ]);
            Session::setSuccessBag('Login key created!');
        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
        }
        return 'redirect: /profile/developer';
    }

    /**
     * @Route ("/profile/authtoken/{tokenId}/delete")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     * @throws Exception
     */
    public function accessTokenDelete(array $params): string {
        FilterParams::required($params, 'tokenId');
        $oauthService = DggOAuthService::instance();
        $userId = Session::getCredentials()->getUserId();
        $accessToken = $oauthService->getAccessTokenById($params['tokenId']);
        if (empty($accessToken)) {
            throw new Exception ('Invalid access token');
        }
        if ($accessToken['userId'] != $userId) {
            throw new Exception ('Access token not owned by user');
        }
        $oauthService->removeAccessToken($params['tokenId']);
        Session::setSuccessBag('Access token removed!');
        return 'redirect: /profile/developer';
    }

    /**
     * @Route ("/profile/connect/{provider}")
     * @Secure ({"USER"})
     * @throws Exception
     */
    public function authProfileConnect(array $params): string {
        FilterParams::required($params, 'provider');
        $authProvider = $params ['provider'];

        // Set a session var that is picked up in the AuthenticationService
        // in the GET method, this variable is unset
        Session::set('isConnectingAccount', '1');

        switch (strtoupper($authProvider)) {
            case 'TWITCH' :
                $authHandler = new TwitchAuthHandler ();
                return 'redirect: ' . $authHandler->getAuthorizationUrl();

            case 'GOOGLE' :
                $authHandler = new GoogleAuthHandler ();
                return 'redirect: ' . $authHandler->getAuthorizationUrl();

            case 'TWITTER' :
                $authHandler = new TwitterAuthHandler ();
                return 'redirect: ' . $authHandler->getAuthorizationUrl();

            case 'REDDIT' :
                $authHandler = new RedditAuthHandler ();
                return 'redirect: ' . $authHandler->getAuthorizationUrl();

            case 'DISCORD' :
                $authHandler = new DiscordAuthHandler ();
                return 'redirect: ' . $authHandler->getAuthorizationUrl();

            default :
                throw new Exception ('Authentication type not supported');
        }
    }

    /**
     * @Route ("/profile/remove")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     * @throws Exception
     */
    public function removeAuthProfiles(array $params): string {
        $userId = Session::getCredentials()->getUserId();
        FilterParams::isArray($params, 'selected');
        $userAuthService = UserAuthService::instance();
        foreach ($params['selected'] as $id) {
            $userAuthService->removeByIdAndUserId((int) $id, $userId);
        }
        Session::setSuccessBag("Login provider(s) removed");
        return 'redirect: /profile/authentication';
    }

    /**
     * @Route ("/profile/gifts")
     * @HttpMethod ({"GET"})
     * @Secure ({"USER"})
     * @throws Exception
     */
    function gifts(ViewModel $model): string {
        $userId = Session::getCredentials ()->getUserId ();
        $model->gifts = SubscriptionsService::instance()->findCompletedByGifterId($userId);
        $model->user = UserService::instance()->getUserById($userId);
        return 'profile/gifts';
    }

    /**
     * @Route ("/profile/donations")
     * @HttpMethod ({"GET"})
     * @Secure ({"USER"})
     * @throws Exception
     */
    function donations(ViewModel $model): string {
        $userId = Session::getCredentials ()->getUserId ();
        $model->donations = DonationService::instance()->findCompletedByUserId($userId);
        $model->user = UserService::instance()->getUserById($userId);
        return 'profile/donations';
    }

    /**
     * @Route ("/profile/subscriptions")
     * @HttpMethod ({"GET"})
     * @Secure ({"USER"})
     * @throws Exception
     */
    function subscriptions(ViewModel $model): string {
        $userId = Session::getCredentials ()->getUserId ();
        $model->subscriptions = SubscriptionsService::instance()->findCompletedByUserId($userId);
        $model->user = UserService::instance()->getUserById($userId);
        return 'profile/subscriptions';
    }

    /**
     * @Route ("/profile/discord/update")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     * @throws Exception
     */
    public function updateDiscord(array $params): string {
        $userService = UserService::instance();
        $userId = Session::getCredentials()->getUserId();
        FilterParams::declared($params, 'discordname');
        $data = ['discordname' => $params['discordname']];
        if (trim($data['discordname']) == '')
            $data['discordname'] = null;
        if (mb_strlen($data['discordname']) > 36) {
            Session::setErrorBag('Discord username too long.');
            return 'redirect: /profile';
        }
        $uId = $userService->getUserIdByField('discordname', $params['discordname']);
        if ($data['discordname'] == null || empty($uId) || intval($uId) === intval($userId)) {
            $userService->updateUser($userId, $data);
            Session::setSuccessBag('Discord info has been updated');
        } else {
            Session::setErrorBag('Discord name already in use');
        }
        return 'redirect: /profile';
    }

    /**
     * @Route ("/profile/minecraft/update")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     * @throws Exception
     */
    public function updateMinecraft(array $params): string {
        $userService = UserService::instance();
        $userId = Session::getCredentials()->getUserId();
        FilterParams::declared($params, 'minecraftname');
        $data = ['minecraftname' => $params['minecraftname']];
        if (trim($data['minecraftname']) == '')
            $data['minecraftname'] = null;
        if (mb_strlen($data['minecraftname']) > 16) {
            Session::setErrorBag('Minecraft name too long.');
            return 'redirect: /profile';
        }
        $uId = $userService->getUserIdByField('minecraftname', $params['minecraftname']);
        if ($data['minecraftname'] == null || empty($uId) || intval($uId) === intval($userId)) {
            $userService->updateUser($userId, $data);
            Session::setSuccessBag('Minecraft name has been updated');
        } else {
            Session::setErrorBag('Minecraft name already in use');
        }
        return 'redirect: /profile';
    }

    /**
     * @Route ("/profile/auth/youtube")
     * @HttpMethod ({"GET"})
     * @Secure ({"USER"})
     */
    public function authYouTube(): string {
        return 'redirect: ' . YouTubeAuthHandler::instance()->getAuthorizationUrl();
    }

    /**
     * @Route ("/profile/auth/youtube/cb")
     * @HttpMethod ({"GET"})
     * @Secure ({"USER"})
     */
    public function authYouTubeCallback(array $params): string {
        $response = YouTubeAuthHandler::instance()->exchangeCode($params);
        $userId = Session::getCredentials()->getUserId();
        UserAuthService::instance()->saveUserAuthWithOAuth($response, $userId);

        try {
            $channels = YouTubeApiService::instance()->getChannelsForUserId($userId);
            YouTubeMembershipService::instance()->addChannelsForUserId($channels, $userId);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            Session::setErrorBag('Error syncing YouTube channels. Please try again later.');
            return 'redirect: /profile';
        }

        AuthenticationService::instance()->flagUserForUpdate($userId);
        Session::setSuccessBag('Authorization completed successfully!');
        return 'redirect: /profile';
    }

    /**
     * @throws Exception
     */
    private function validateAppName(string $name) {
        if (preg_match('/^[A-Za-z0-9 ]{3,64}$/', $name) == 0) {
            throw new Exception ('Name may only contain A-z 0-9 or spaces and must be over 3 characters and under 64 characters in length.');
        }
    }

    /**
     * @throws Exception
     */
    private function validateAppRedirect(string $redirect) {
        if (mb_strlen($redirect) > 255) {
            throw new Exception ('Redirect URL has exceeded max length of 255 characters.');
        }
    }

    /**
     * @Route ("/profile/delete")
     * @HttpMethod ({"POST"})
     * @Secure ({"USER"})
     */
    public function deleteAccount(Request $request): string {
        try {
            $googleRecaptchaHandler = new GoogleRecaptchaHandler();
            $googleRecaptchaHandler->resolveWithRequest($request);
            $creds = Session::getCredentials();
            $userId = $creds->getUserId();

            $userServer = UserService::instance();
            $userServer->updateUser($userId, ['userStatus' => UserStatus::REDACTED]);

            $redis = ChatRedisService::instance();
            $redis->removeChatSession(Session::getSessionId());

            DiscordMessenger::send('Account deletion request', ['fields' => [['title' => 'User', 'value' => DiscordMessenger::userLink($creds->getUserId(), $creds->getUsername()), 'short' => false]]]);

            AuthenticationService::instance()->removeWebSession();
            return 'profile/deleted';
        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
            return 'redirect: /profile';
        }
    }

}
