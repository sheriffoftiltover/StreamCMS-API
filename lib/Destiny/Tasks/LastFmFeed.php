<?php

namespace Destiny\Tasks;

use Destiny\Common\Application;
use Destiny\LastFm\LastFMApiService;
use Psr\Log\LoggerInterface;

class LastFmFeed
{

    public function execute(LoggerInterface $log)
    {
        $app = Application::instance();
        $response = LastFMApiService::instance()->getLastFMTracks()->getResponse();
        if (!empty ($response))
            $app->getCacheDriver()->save('recenttracks', $response);
    }

}