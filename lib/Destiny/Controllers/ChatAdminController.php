<?php
declare(strict_types=1);

namespace Destiny\Controllers;

use Destiny\Chat\ChatIntegrationService;
use Destiny\Common\Exception;
use Destiny\Common\Session;
use Destiny\Common\User\UserService;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\ViewModel;

/**
 * @Controller
 */
class ChatAdminController
{

    /**
     * @Route ("/admin/chat")
     * @Secure ({"ADMIN"})
     * @HttpMethod ({"GET"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     */
    public function adminChat(array $params, ViewModel $model)
    {
        $model->title = 'Chat';
        if (Session::get('modelSuccess')) {
            $model->success = Session::get('modelSuccess');
            Session::set('modelSuccess');
        }
        if (Session::get('modelError')) {
            $model->error = Session::get('modelError');
            Session::set('modelError');
        }
        return 'admin/chat';
    }

    /**
     * @Route ("/admin/chat/broadcast")
     * @Secure ({"ADMIN"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     */
    public function adminChatBroadcast(array $params, ViewModel $model)
    {
        $model->title = 'Chat';
        FilterParams::isRequired($params, 'message');

        $chatIntegrationService = ChatIntegrationService::instance();
        $chatIntegrationService->sendBroadcast($params ['message']);

        Session::set('modelSuccess', sprintf('Sent broadcast: %s', $params ['message']));
        return 'redirect: /admin/chat';
    }

    /**
     * @Route ("/admin/chat/ip")
     * @Secure ({"ADMIN"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     */
    public function adminChatIp(array $params, ViewModel $model)
    {
        $model->title = 'Chat';
        FilterParams::isRequired($params, 'ip');

        $userService = UserService::instance();
        $model->usersByIp = $userService->findUsersWithIP($params ['ip']);
        $model->searchIp = $params ['ip'];

        return 'admin/chat';
    }

}
