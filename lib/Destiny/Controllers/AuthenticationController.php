<?php

namespace Destiny\Controllers;

use Destiny\Api\ApiAuthHandler;
use Destiny\Common\Exception;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Common\ViewModel;
use Destiny\Google\GoogleAuthHandler;
use Destiny\Reddit\RedditAuthHandler;
use Destiny\Twitch\TwitchAuthHandler;
use Destiny\Twitter\TwitterAuthHandler;

/**
 * @Controller
 */
class AuthenticationController
{

    /**
     * @Route ("/auth/api")
     * @Transactional
     *
     * @param array $params
     * @param ViewModel $model
     * @throws Exception
     */
    public function authApi(array $params, ViewModel $model)
    {
        try {
            $authHandler = new ApiAuthHandler ();
            return $authHandler->authenticate($params, $model);
        } catch (\Exception $e) {
            $response = new Response (Http::STATUS_ERROR, $e->getMessage());
            return $response;
        }
    }

    /**
     * @Route ("/auth/twitch")
     * @Transactional
     *
     * @param array $params
     * @param ViewModel $model
     * @throws Exception
     */
    public function authTwitch(array $params, ViewModel $model)
    {
        try {
            $authHandler = new TwitchAuthHandler ();
            return $authHandler->authenticate($params, $model);
        } catch (\Exception $e) {
            $model->title = 'Login error';
            $model->error = $e;
            return 'login';
        }
    }

    /**
     * @Route ("/auth/twitter")
     * @Transactional
     *
     * @param array $params
     * @param ViewModel $model
     * @throws Exception
     */
    public function authTwitter(array $params, ViewModel $model)
    {
        try {
            $authHandler = new TwitterAuthHandler ();
            return $authHandler->authenticate($params, $model);
        } catch (\Exception $e) {
            $model->title = 'Login error';
            $model->error = $e;
            return 'login';
        }
    }

    /**
     * @Route ("/auth/google")
     * @Transactional
     *
     * @param array $params
     * @param ViewModel $model
     * @throws Exception
     */
    public function authGoogle(array $params, ViewModel $model)
    {
        try {
            $authHandler = new GoogleAuthHandler ();
            return $authHandler->authenticate($params, $model);
        } catch (\Exception $e) {
            $model->title = 'Login error';
            $model->error = $e;
            return 'login';
        }
    }

    /**
     * @Route ("/auth/reddit")
     * @Transactional
     *
     * @param array $params
     * @param ViewModel $model
     * @throws Exception
     */
    public function authReddit(array $params, ViewModel $model)
    {
        try {
            $authHandler = new RedditAuthHandler ();
            return $authHandler->authenticate($params, $model);
        } catch (\Exception $e) {
            $model->title = 'Login error';
            $model->error = $e;
            return 'login';
        }
    }
}