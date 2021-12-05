<?php
namespace Destiny\Common\Authentication;

use DateInterval;
use DateTime;
use Destiny\Chat\ChatRedisService;
use Destiny\Chat\EmoteService;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Service;
use Destiny\Common\Session\Session;
use Destiny\Common\Session\SessionCredentials;
use Destiny\Common\User\UserFeature;
use Destiny\Common\User\UserRole;
use Destiny\Common\User\UserService;
use Destiny\Common\Utils\CryptoOpenSSL;
use Destiny\Common\Utils\Date;
use Destiny\Discord\DiscordAuthHandler;
use Destiny\Google\GoogleAuthHandler;
use Destiny\Reddit\RedditAuthHandler;
use Destiny\Reddit\RedditService;
use Destiny\StreamElements\StreamElementsAuthHandler;
use Destiny\StreamLabs\StreamLabsAuthHandler;
use Destiny\Twitch\TwitchAuthHandler;
use Destiny\Twitch\TwitchBroadcastAuthHandler;
use Destiny\Twitter\TwitterAuthHandler;
use Destiny\YouTube\YouTubeMembershipService;
use Destiny\YouTube\YouTubeBroadcasterAuthHandler;

/**
 * @method static AuthenticationService instance()
 */
class AuthenticationService extends Service {

    const REGEX_VALID_USERNAME = '/^[A-Za-z0-9_]{3,20}$/';
    const REGEX_REPLACE_CHAR_USERNAME = '/[^A-Za-z0-9_]/';
    const USERNAME_MIN = 3;
    const USERNAME_MAX = 20;

    /**
     * @throws Exception
     */
    public function validateUsername(string $username) {
        if (empty($username)) {
            throw new Exception ('Username required');
        }
        if (preg_match(self::REGEX_VALID_USERNAME, $username) == 0) {
            throw new Exception ('Username may only contain A-z 0-9 or underscores and must be over 3 characters and under 20 characters in length.');
        }
        if (preg_match_all('/[0-9]{3}/', $username, $m) > 0) {
            throw new Exception ('Too many numbers in a row in username');
        }
        if (preg_match_all('/[_]{2}/', $username, $m) > 0 || preg_match_all("/[_]+/", $username, $m) > 2) {
            throw new Exception ('Too many underscores in username');
        }
        if (preg_match_all("/[0-9]/", $username, $m) > round(strlen($username) / 2)) {
            throw new Exception ('Number ratio is too high in username');
        }

        $normalizedUsername = strtolower($username);

        // nick blacklists
        $blacklist = array_merge([], include _BASEDIR . '/config/nick.blacklist.php');
        if (in_array($normalizedUsername, $blacklist)) {
            throw new Exception ('Username is blacklisted');
        }
    }

    /**
     * Checks if a username is "too similar" to an emote prefix.
     *
     * @throws Exception
     */
    public function checkUsernameForSimilarityToEmote(string $username, string $emotePrefix) {
        $normalizedUsername = strtolower($username);
        $normalizedPrefix = strtolower($emotePrefix);

        // Ensure the username doesn't match the emote exactly. The username
        // `Jamstiny` fails validation if an emote with prefix `JAMSTINY`
        // exists.
        if (strcmp($normalizedUsername, $normalizedPrefix) === 0) {
            throw new Exception("Username is invalid because it matches the emote $emotePrefix.");
        }

        // Ensure the beginning of the username doesn't contain the entire emote
        // prefix. `Jamstinycakes` fails if `JAMSTINY` exists.
        if (strpos($normalizedUsername, $normalizedPrefix) === 0) {
            throw new Exception("Username is invalid because it starts with the emote $emotePrefix.");
        }

        // Don't perform the check below for the `LUL` emote.
        if ($normalizedPrefix == strtolower(EmoteService::LUL_EMOTE_PREFIX)) {
            return;
        }

        // If the first two letters of the username and the emote prefix match,
        // ensure the Levenshtein distance between the first `n` letters of the
        // username (where `n` is the length of the emote prefix) and the emote
        // prefix is <= 2. `Japstiny` fails because the distance between
        // `japstiny` and `jamstiny` is 1. Only one letter has to be
        // replaced/inserted/deleted to change `japstiny` to `jamstiny`.
        $usernamePrefix = substr($normalizedUsername, 0, strlen($normalizedPrefix));
        if (substr($normalizedUsername, 0, 2) == substr($normalizedPrefix, 0, 2) && levenshtein($normalizedPrefix, $usernamePrefix) <= 2) {
            throw new Exception("Username is invalid because it has too many like characters to the emote $emotePrefix.");
        }
    }

    /**
     * Checks if a username is "too similar" to any/all available emote prefixes.
     *
     * @throws Exception
     */
    public function checkUsernameForSimilarityToAllEmotes(string $username) {
        foreach ($this->getEmotesForValidation() as $emote) {
            $this->checkUsernameForSimilarityToEmote($username, $emote['prefix']);
        }
    }

    /**
     * Gets emote prefixes that are "too similar" to a username.
     *
     * @return array Contains all emote prefixes that are too similar to the supplied username.
     */
    public function getEmotesTooSimilarToUsername(string $username): array {
        $conflictingEmotes = [];
        foreach ($this->getEmotesForValidation() as $emote) {
            $prefix = $emote['prefix'];
            try {
                $this->checkUsernameForSimilarityToEmote($username, $prefix);
            } catch (Exception $e) {
                $conflictingEmotes[] = $prefix;
            }
        }

        return $conflictingEmotes;
    }

    /**
     * @throws Exception
     */
    public function validateEmail(string $email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception ('A valid email is required');
        }
        $emailDomain = strtolower(substr($email, strpos($email, '@') + 1));
        $blacklist = array_merge([], include _BASEDIR . '/config/domains.blacklist.php');
        if (in_array($emailDomain, $blacklist)) {
            throw new Exception ('email is blacklisted');
        }
    }

    /**
     * Starts up the session, looks for remember me if there was no session
     * Also updates the session if the user is flagged for it.
     * TODO this method is a mess
     *
     * @throws Exception
     */
    public function startSession() {
        $redisService = ChatRedisService::instance();
        $userService = UserService::instance();

        // If the session has a cookie, start it
        $app = Application::instance();
        $session = $app->getSession();
        $cookie = $app->getSessionCookie();
        if (!empty($session) || !empty($cookie)) {
            $session->setupCookie($cookie);
        }
        //

        $sid = $cookie->getValue();
        if (!empty($sid) && Session::start() && Session::hasRole(UserRole::USER)) {
            $sessionId = Session::getSessionId();
            if (!empty($sessionId)) {
                $success = $redisService->renewChatSessionExpiration($sessionId);

                // If the renewal failed, then no chat session exists. A new
                // session should be created if the user is allowed to chat.
                if (!$success) {
                    $creds = Session::getCredentials();
                    $user = $userService->getUserById($creds->getUserId());
                    if ($user['allowChatting']) {
                        $redisService->setChatSession($creds, $sessionId);
                    }
                }
            }
        }

        // Check the Remember me cookie if the session is invalid
        if (!Session::hasRole(UserRole::USER)) {
            $user = $this->getRememberMe();
            if (!empty($user)) {
                Session::start();
                $this->updateWebSession($user);
                $this->setRememberMe($user);
            }
        }

        // Update the user if they have been flagged for an update
        if (Session::hasRole(UserRole::USER)) {
            $creds = Session::getCredentials();
            $userId = $creds->getUserId();
            if (!empty($userId) && $this->isUserFlaggedForUpdate($userId)) {
                $user = $userService->getUserById($userId);
                $this->clearUserUpdateFlag($userId);
                $this->updateWebSession($user, $creds->getAuthProvider());
            }
        }
    }

    /**
     * @throws Exception
     */
    public function buildUserCredentials(array $user, string $authProvider = ''): SessionCredentials {
        $userService = UserService::instance();
        $subscriptionService = SubscriptionsService::instance();
        $creds = new SessionCredentials($user);
        $creds->setAuthProvider($authProvider);
        $creds->addRoles(UserRole::USER);
        $creds->addFeatures($userService->getFeaturesByUserId($user ['userId']));
        $creds->addRoles($userService->getRolesByUserId($user ['userId']));

        if ($user['istwitchsubscriber']) {
            $creds->addFeatures(UserFeature::SUBSCRIBER_TWITCH);
        }

        $sub = $subscriptionService->getUserActiveSubscription($user['userId']);
        if (Config::$a[AuthProvider::YOUTUBE_BROADCASTER]['sync_memberships']) {
            $youTubeMembership = YouTubeMembershipService::instance()->getMembershipDetailsForUserId($user['userId']);
        }

        if (!empty ($sub)) {
            $creds->addRoles(UserRole::SUBSCRIBER);
            $creds->addFeatures(UserFeature::SUBSCRIBER);
            switch ($sub['subscriptionTier']) {
                case 1:
                    $creds->addFeatures(UserFeature::SUBSCRIBERT1);
                    break;
                case 2:
                    $creds->addFeatures(UserFeature::SUBSCRIBERT2);
                    break;
                case 3:
                    $creds->addFeatures(UserFeature::SUBSCRIBERT3);
                    break;
                case 4:
                    $creds->addFeatures(UserFeature::SUBSCRIBERT4);
                    break;
            }
            $creds->setSubscription([
                'tier' => $sub['subscriptionTier'],
                'source' => $sub['subscriptionSource'],
                'type' => $sub['subscriptionType'],
                'start' => Date::getDateTime($sub['createdDate'])->format(Date::FORMAT),
                'end' => Date::getDateTime($sub['endDate'])->format(Date::FORMAT)
            ]);
        } else if (!empty($youTubeMembership)) {
            $creds->addRoles(UserRole::SUBSCRIBER);
            $creds->addFeatures(UserFeature::SUBSCRIBER);
            switch($youTubeMembership['name']) {
                case Config::$a['commerce']['tiers'][0]['tierLabel']:
                    $creds->addFeatures(UserFeature::SUBSCRIBERT1);
                    break;
                case Config::$a['commerce']['tiers'][1]['tierLabel']:
                    $creds->addFeatures(UserFeature::SUBSCRIBERT2);
                    break;
                case Config::$a['commerce']['tiers'][2]['tierLabel']:
                    $creds->addFeatures(UserFeature::SUBSCRIBERT3);
                    break;
                case Config::$a['commerce']['tiers'][3]['tierLabel']:
                    $creds->addFeatures(UserFeature::SUBSCRIBERT4);
                    break;
            }
        } else if ($user['istwitchsubscriber']) {
            $creds->addRoles(UserRole::SUBSCRIBER);
            $creds->addFeatures(UserFeature::SUBSCRIBER);
            $creds->setSubscription([
                'tier' => 1,
                'source' => 'twitch.tv',
                'type' => null,
                'start' => null,
                'end' => null
            ]);
        }

        return $creds;
    }

    /**
     * Generates a rememberme cookie
     * Note the rememberme cookie has a long expiry unlike the session cookie
     */
    public function setRememberMe(array $user) {
        $app = Application::instance();
        $cookie = $app->getRememberMeCookie();
        if (empty($cookie)) {
            return;
        }
        $rawData = $cookie->getValue();
        if (!empty($rawData)) {
            $cookie->clearCookie();
        }
        $expires = Date::getDateTime(time() + mt_rand(0, 2419200)); // 0-28 days
        $expires->add(new DateInterval('P1M'));
        $data = CryptoOpenSSL::encrypt(serialize([
            'userId' => $user['userId'],
            'expires' => $expires->getTimestamp()
        ]));
        $cookie->setValue($data, Date::getDateTime('NOW + 2 MONTHS')->getTimestamp());
    }

    /**
     * Returns the user record associated with a remember me cookie
     * @return array|null
     */
    protected function getRememberMe() {
        $app = Application::instance();
        $user = null;

        $cookie = $app->getRememberMeCookie();
        if (!$cookie) goto end;

        $rawData = $cookie->getValue();
        if (empty($rawData)) goto end;

        if (strlen($rawData) < 64) goto cleanup;

        $data = CryptoOpenSSL::decrypt($rawData);

        if (!$data)
            goto cleanup;

        $data = unserialize($data);
        if (!isset($data['expires']) or !isset($data['userId']))
            goto cleanup;

        $expires = Date::getDateTime($data['expires']);
        if ($expires <= Date::getDateTime())
            goto cleanup;

        try {
            $userService = UserService::instance();
            $user = $userService->getUserById(intval($data['userId']));
        } catch (Exception $e) {
            Log::error("Error getting remember me user. {$e->getMessage()}");
        }
        goto end;

        cleanup:
        $cookie->clearCookie();
        end:
        return $user;
    }

    /**
     * @throws Exception
     */
    public function updateWebSession(array $user, string $provider = '') {
        try {
            $credentials = $this->buildUserCredentials($user, $provider);
            //
            $session = Session::instance();
            foreach ($credentials->getData() as $name => $value) {
                $session->set($name, $value);
            }
            $session->setCredentials($credentials);
            //
            $redisService = ChatRedisService::instance();
            $sessionId = Session::getSessionId();
            if (boolval($user['allowChatting'])) {
                $redisService->setChatSession($credentials, $sessionId);
                $redisService->sendRefreshUser($credentials);
            } else {
                $redisService->removeChatSession($sessionId);
            }
        } catch (Exception $e) {
            throw new Exception("Cannot update web session.", $e);
        }
    }

    public function removeWebSession() {
        if (Session::isStarted()) {
            $session = Session::instance();
            if (!empty($session)) {
                $app = Application::instance();
                $app->getSessionCookie()->clearCookie();
                $app->getRememberMeCookie()->clearCookie();
                $redis = ChatRedisService::instance();
                $redis->removeChatSession($session->getSessionId());
                $session->destroy();
            }
        }
    }

    /**
     * Flag a user session for update
     * So that on their next request, the session data is updated.
     * Also does a chat session refresh
     */
    public function flagUserForUpdate(int $userId) {
        try {
            $userService = UserService::instance();
            $user = $userService->getUserById($userId);
            if (!empty($user)) {
                $creds = $this->buildUserCredentials($user);
                $this->setUserUpdateFlag($userId);
                $redisService = ChatRedisService::instance();
                $redisService->sendRefreshUser($creds);
            }
        } catch (Exception $e) {
            Log::error("Error flagging user for update. {$e->getMessage()}");
        }
    }

    /**
     * Checks if the auth provider account meets all requirements to connect.
     */
    public function validateAuthAccountDetails(OAuthResponse $oauthResponse): bool {
        switch ($oauthResponse->getAuthProvider()) {
            case AuthProvider::REDDIT:
                $redditService = RedditService::instance(); 
                $userIdentity = $redditService->getUserIdentity($oauthResponse->getAccessToken());
                if (empty($userIdentity)) {
                    throw new Exception('There was an error validating your account. Please try again later.');
                }

                $minimumAccountAgeDays = Config::$a['oauth_providers']['reddit']['minimum_account_age_days'];
                $accountCreationDate = (new DateTime())->setTimestamp($userIdentity['created_utc']);
                $accountAge = date_diff(new DateTime(), $accountCreationDate);
                $isOldEnough = $accountAge->days >= $minimumAccountAgeDays;

                $minimumKarma = Config::$a['oauth_providers']['reddit']['minimum_karma'];
                $hasEnoughKarma = $userIdentity['total_karma'] >= $minimumKarma;

                Log::debug("Reddit account age: {$accountAge->days}");
                Log::debug("Reddit account total karma: {$userIdentity['total_karma']}");

                if (!$isOldEnough || !$hasEnoughKarma) {
                    throw new Exception("Your reddit account needs at least $minimumKarma karma and must be at least $minimumAccountAgeDays days old to connect.");
                }

                return true;
            default:
                return true;
        }
    }

    private function isUserFlaggedForUpdate(int $userId): bool {
        $cache = Application::getNsCache();
        $lastUpdated = $cache->fetch("refreshusersession-$userId");
        return !empty($lastUpdated);
    }

    private function clearUserUpdateFlag(int $userId) {
        $cache = Application::getNsCache();
        $cache->delete("refreshusersession-$userId");
    }

    private function setUserUpdateFlag(int $userId) {
        $cache = Application::getNsCache();
        $cache->save("refreshusersession-$userId", time(), intval(ini_get('session.gc_maxlifetime')));
    }

    private function getEmotesForValidation(): array {
        try {
            return EmoteService::instance()->findAllEmotes();
        } catch (Exception $e) {
            Log::error("Emote failed to load. {$e->getMessage()}");
        }
        return [];
    }

    /**
     * @throws Exception
     */
    public function getLoginAuthHandlerByType(string $type): AuthenticationHandler {
        $authHandler = null;
        switch (strtolower($type)) {
            case AuthProvider::TWITCH:
                $authHandler = new TwitchAuthHandler();
                break;
            case AuthProvider::TWITTER:
                $authHandler = new TwitterAuthHandler();
                break;
            case AuthProvider::GOOGLE:
                $authHandler = new GoogleAuthHandler();
                break;
            case AuthProvider::YOUTUBE:
                $authHandler = new YouTubeAuthHandler();
                break;
            case AuthProvider::YOUTUBE_BROADCASTER:
                $authHandler = new YouTubeBroadcasterAuthHandler();
                break;
            case AuthProvider::REDDIT:
                $authHandler = new RedditAuthHandler();
                break;
            case AuthProvider::DISCORD:
                $authHandler = new DiscordAuthHandler();
                break;
            case AuthProvider::STREAMELEMENTS:
                $authHandler = new StreamElementsAuthHandler();
                break;
            case AuthProvider::STREAMLABS:
                $authHandler = new StreamLabsAuthHandler();
                break;
            case AuthProvider::TWITCHBROADCAST:
                $authHandler = new TwitchBroadcastAuthHandler();
                break;
            default:
                throw new Exception('No authentication handler found.');
        }
        return $authHandler;
    }
}
