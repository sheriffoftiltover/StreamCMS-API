<?php

namespace Destiny\Controllers;

use Destiny\Common\Request;
use Destiny\Common\Session;
use Destiny\Common\User\UserService;
use Destiny\Common\ViewModel;

/**
 * @Controller
 */
class BannedController
{

    /**
     * @Route ("/banned")
     * @Secure ({"USER"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function banned(array $params, ViewModel $model, Request $request)
    {
        $userService = UserService::instance();
        $creds = Session::getCredentials();
        $model->ban = $userService->getUserActiveBan($creds->getUserId(), $request->ipAddress());
        $model->banType = 'none';
        if (!empty ($model->ban)) {
            if (!$model->ban ['endtimestamp']) {
                $model->banType = 'permanent';
            } else {
                $model->banType = 'temporary';
            }
        }
        $model->user = $creds->getData();
        return 'banned';
    }
}
