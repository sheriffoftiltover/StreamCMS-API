<?php
declare(strict_types=1);

namespace Destiny\Controllers;

use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Common\ViewModel;

/**
 * @Controller
 */
class HomeController
{

    /**
     * @Route ("/")
     * @Route ("/home")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function home(array $params, ViewModel $model)
    {
        $app = Application::instance();
        $cacheDriver = $app->getCacheDriver();
        $model->articles = $cacheDriver->fetch('recentblog');
        $model->summoners = $cacheDriver->fetch('summoners');
        $model->tweets = $cacheDriver->fetch('twitter');
        $model->music = $cacheDriver->fetch('recenttracks');
        $model->playlist = $cacheDriver->fetch('youtubeplaylist');
        $model->broadcasts = $cacheDriver->fetch('pastbroadcasts');
        $model->streamInfo = $cacheDriver->fetch('streaminfo');
        return 'home';
    }

    /**
     * @Route ("/help/agreement")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function helpAgreement(array $params, ViewModel $model)
    {
        $model->title = 'User agreement';
        return 'help/agreement';
    }

    /**
     * @Route ("/ping")
     *
     * @param array $params
     */
    public function ping(array $params)
    {
        $response = new Response (Http::STATUS_OK);
        $response->addHeader('X-Pong', 'Destiny');
        return $response;
    }

    /**
     * @Route ("/screen")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function screen(array $params, ViewModel $model)
    {
        $app = Application::instance();
        $cacheDriver = $app->getCacheDriver();
        $model->articles = $cacheDriver->fetch('recentblog');
        $model->summoners = $cacheDriver->fetch('summoners');
        $model->tweets = $cacheDriver->fetch('twitter');
        $model->music = $cacheDriver->fetch('recenttracks');
        $model->playlist = $cacheDriver->fetch('youtubeplaylist');
        $model->broadcasts = $cacheDriver->fetch('pastbroadcasts');
        $model->streamInfo = $cacheDriver->fetch('streaminfo');
        return 'screen';
    }

    /**
     * @Route ("/bigscreen")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function bigscreen(array $params, ViewModel $model)
    {
        $model->streamInfo = Application::instance()->getCacheDriver()->fetch('streaminfo');
        return 'bigscreen';
    }

    /**
     * @Route ("/emotes")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function emoticons(array $params, ViewModel $model)
    {
        $emotes = Config::$a['chat'] ['customemotes'];
        natcasesort($emotes);
        $model->emoticons = $emotes;
        return 'chat/emoticons';
    }

    /**
     * @Route ("/shave")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function shave(array $params, ViewModel $model)
    {
        $model->url = 'http://dollar-shave-club.7eer.net/c/72409/74122/1969';
        return 'outbound';
    }

    /**
     * @Route ("/ting")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function ting(array $params, ViewModel $model)
    {
        $model->url = 'http://ting.7eer.net/c/72409/87559/2020';
        return 'outbound';
    }

    /**
     * @Route ("/amazon")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function amazon(array $params, ViewModel $model)
    {
        $model->url = 'http://www.amazon.com/?tag=des000-20';
        return 'outbound';
    }

    /**
     * @Route ("/eve")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function eve(array $params, ViewModel $model)
    {
        $model->url = 'https://secure.eveonline.com/trial/?invc=7a8cfcda-5915-4297-9cf9-ed898d984ff2&action=buddy';
        return 'outbound';
    }

    /**
     * @Route ("/schedule")
     *
     * @return string
     */
    public function schedule()
    {
        return 'redirect: https://www.google.com/calendar/embed?src=i54j4cu9pl4270asok3mqgdrhk%40group.calendar.google.com';
    }

    /**
     * @Route ("/i")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     */
    public function tournament(array $params, ViewModel $model)
    {
        return 'redirect: http://i.destiny.gg/';
    }

}
