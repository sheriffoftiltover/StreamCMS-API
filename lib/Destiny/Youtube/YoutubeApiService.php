<?php
declare(strict_types=1);

namespace Destiny\Youtube;

use Destiny\Common\Config;
use Destiny\Common\CurlBrowser;
use Destiny\Common\Exception;
use Destiny\Common\MimeType;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\String;

class YoutubeApiService extends Service
{

    /**
     * Singleton
     *
     * @return YoutubeApiService
     */
    protected static $instance = null;

    /**
     * Singleton
     *
     * @return YoutubeApiService
     */
    public static function instance()
    {
        return parent::instance();
    }

    /**
     * Get a the latest playlist from google
     *
     * @param array $options
     * @param array $params
     * @return \Destiny\CurlBrowser
     * @throws Exception
     */
    public function getYoutubePlaylist(array $options = [], array $params = [])
    {
        // Get the channel ID's from a specific person
        // GET https://www.googleapis.com/youtube/v3/channels?part=contentDetails&forUsername=StevenBonnell&key={YOUR_API_KEY}
        $params ['limit'] = (isset ($params ['limit'])) ? intval($params ['limit']) : 4;
        return new CurlBrowser (array_merge([
            'url' => new String ('https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId={playlistId}&key={apikey}&maxResults={limit}', ['playlistId' => Config::$a ['youtube'] ['playlistId'], 'apikey' => Config::$a ['youtube'] ['apikey'], 'limit' => $params ['limit']]), 'contentType' => MimeType::JSON, 'onfetch' => function ($json)
        {
            if (is_array($json ['items'])) {
                foreach ($json ['items'] as $i => $item) {
                    $item ['snippet'] ['publishedAt'] = Date::getDateTime($item ['snippet'] ['publishedAt'], Date::FORMAT);
                }
            } else {
                throw new Exception ('Youtube API Down');
            }
            return $json;
        }
        ], $options));
    }

}