<?php
declare(strict_types=1);

namespace Destiny\Twitch;

use DateInterval;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\CurlBrowser;
use Destiny\Common\Exception;
use Destiny\Common\MimeType;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\String;

class TwitchApiService extends Service
{

    protected static $instance = null;
    /**
     * Stored when the broadcaster logs in, used to retrieve subscription
     *
     * @var string
     */
    protected $token = '';

    /**
     * @param array $options
     * @return ApiConsumer
     */
    public function getPastBroadcasts(array $options = [])
    {
        return new CurlBrowser (array_merge(['timeout' => 25, 'url' => new String ('https://api.twitch.tv/kraken/channels/{user}/videos?broadcasts=true&limit={limit}', ['user' => Config::$a ['twitch'] ['user'], 'limit' => 4]), 'contentType' => MimeType::JSON], $options));
    }

    /**
     * @return ApiConsumer
     */
    public function getStreamInfo(array $options = [])
    {
        return new CurlBrowser (array_merge([
            'url' => new String ('https://api.twitch.tv/kraken/streams/{user}/', ['user' => Config::$a ['twitch'] ['user']]), 'contentType' => MimeType::JSON, 'onfetch' => function ($json)
        {

            if (empty($json) || (isset ($json ['status']) && $json ['status'] == 503)) {
                throw new Exception ('Twitch api down');
            }

            // Last broadcast if the stream is offline
            // Called via static method, because we are in a closure
            $channel = TwitchApiService::instance()->getChannel()->getResponse();
            if (empty ($channel) || !is_array($channel)) {
                throw new Exception (sprintf('Invalid stream channel response %s', $channel));
            }

            if (is_object($json) && isset ($json ['stream']) && $json ['stream'] != null) {
                $json ['stream'] ['channel'] ['updated_at'] = Date::getDateTime($json ['stream'] ['channel'] ['updated_at'])->format(Date::FORMAT);
            }

            $json ['lastbroadcast'] = Date::getDateTime($channel ['updated_at'])->format(Date::FORMAT);
            $json ['video_banner'] = $channel ['video_banner'];
            $json ['previousbroadcast'] = null;
            $json ['status'] = $channel ['status'];
            $json ['game'] = $channel ['game'];

            // Previous broadcast
            $app = Application::instance();
            $broadcasts = $app->getCacheDriver()->fetch('pastbroadcasts');
            if (!empty ($broadcasts) && !empty ($broadcasts ['videos'])) {
                $broadcast = [];
                $broadcast ['length'] = $broadcasts ['videos'] [0] ['length'];
                $broadcast ['preview'] = $broadcasts ['videos'] [0] ['preview'];
                $broadcast ['url'] = $broadcasts ['videos'] [0] ['url'];
                $broadcast ['recorded_at'] = $broadcasts ['videos'] [0] ['recorded_at'];
                $broadcast ['views'] = $broadcasts ['videos'] [0] ['views'];
                $json ['previousbroadcast'] = $broadcast;

                // If there are previous broadcasts, base the last broadcast time on it, twitch seems to update the channel at random
                $json ['lastbroadcast'] = Date::getDateTime($broadcast ['recorded_at'])->add(new DateInterval('PT' . $broadcast ['length'] . 'S'))->format(Date::FORMAT);

            }

            // Just some clean up
            if (isset ($json ['_links'])) {
                unset ($json ['_links']);
            }
            return $json;
        }
        ], $options));
    }

    /**
     *
     * @param array $options
     * @return ApiConsumer
     */
    public function getChannel(array $options = [])
    {
        return new CurlBrowser (array_merge(['url' => new String ('https://api.twitch.tv/kraken/channels/{user}', ['user' => Config::$a ['twitch'] ['user']]), 'contentType' => MimeType::JSON], $options));
    }

    /**
     * Singleton
     *
     * @return TwitchApiService
     */
    public static function instance()
    {
        return parent::instance();
    }
}