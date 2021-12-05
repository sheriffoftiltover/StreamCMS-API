<?php

namespace Destiny\LastFm;

use Destiny\ApiConsumer;
use Destiny\Common\Config;
use Destiny\Common\CurlBrowser;
use Destiny\Common\Exception;
use Destiny\Common\MimeType;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\String;

class LastFMApiService extends Service
{

    /**
     * Singleton
     *
     * @return LastFMApiService
     */
    protected static $instance = null;

    /**
     * Singleton
     *
     * @return LastFMApiService
     */
    public static function instance()
    {
        return parent::instance();
    }

    /**
     * Get the most recent LastFM tracks
     *
     * @param array $options
     * @return ApiConsumer
     */
    public function getLastFMTracks(array $options = [])
    {
        return new CurlBrowser (array_merge([
            'url' => new String ('http://ws.audioscrobbler.com/2.0/?api_key={apikey}&user={user}&method=user.getrecenttracks&limit=3&format=json', Config::$a ['lastfm']), 'contentType' => MimeType::JSON, 'onfetch' => function ($json)
        {
            if (!$json || isset ($json ['error']) && $json ['error'] > 0 || count($json ['recenttracks'] ['track']) <= 0) {
                throw new Exception ('Error fetching tracks');
            }
            foreach ($json ['recenttracks'] ['track'] as $i => $track) {
                // Timezone DST = -1
                if (!isset ($track ['@attr']) || $track ['@attr'] ['nowplaying'] != true) {
                    if (!empty ($track ['date'])) {
                        $track ['date'] ['uts'] = $track ['date'] ['uts'];
                        $json ['recenttracks'] ['track'] [$i] ['date'] ['uts]'] = $track ['date'] ['uts'];
                        $json ['recenttracks'] ['track'] [$i] ['date_str'] = Date::getDateTime($track ['date'] ['uts'])->format(Date::FORMAT);
                    }
                } else {
                    $json ['recenttracks'] ['track'] [$i] ['date_str'] = '';
                }
            }
            return $json;
        }
        ], $options));
    }

}