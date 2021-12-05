<?php
declare(strict_types=1);

namespace Destiny\Controllers;

use Destiny\Common\Config;
use Destiny\Common\Session;
use Destiny\Common\User\UserRole;
use Destiny\Common\ViewModel;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @Controller
 */
class EmbedController
{

    /**
     * @Route ("/embed/stream")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function embedStream(array $params, ViewModel $model)
    {
        $user = null;
        if (Session::hasRole(UserRole::USER)) {
            $creds = Session::getCredentials();
            $user = [];
            $user ['username'] = $creds->getUsername();
            $user ['features'] = $creds->getFeatures();
        }
        $model->user = $user;
        return 'embed/stream';
    }

    /**
     * @Route ("/embed/chat")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function embedChat(array $params, ViewModel $model)
    {
        $user = null;
        if (Session::hasRole(UserRole::USER)) {
            $creds = Session::getCredentials();
            $user = [];
            $user ['username'] = $creds->getUsername();
            $user ['features'] = $creds->getFeatures();
        }
        $model->options = $this->getChatOptionParams($params);
        $model->user = $user;

        // Login follow url
        if (isset($params['follow']) && !empty($params['follow']) && substr($params['follow'], 0, 1) == '/') {
            $model->follow = $params['follow'];
        }

        return 'embed/chat';
    }

    /**
     * Get the chat params from the get request
     * Make sure they are all valid
     *
     * @param array $params
     */
    #[ArrayShape([
        'host' => 'mixed',
        'port' => 'mixed',
        'maxlines' => 'mixed',
        'emoticons' => 'mixed'
    ])] private function getChatOptionParams(array $params)
    {
        $emotes = Config::$a ['chat'] ['customemotes'];
        natcasesort($emotes);
        return ['host' => Config::$a ['chat'] ['host'], 'port' => Config::$a ['chat'] ['port'], 'maxlines' => Config::$a ['chat'] ['maxlines'], 'emoticons' => array_values($emotes),];
    }
}

