<?php
namespace Destiny\Tasks;

use Destiny\Common\Application;
use Psr\Log\LoggerInterface;
use Destiny\LastFm\LastFMApiService;

class LastFmFeed {

    public function execute(LoggerInterface $log) {
        $app = Application::instance ();
        $response = LastFMApiService::instance ()->getLastFMTracks ()->getResponse ();
        if (! empty ( $response ))
            $app->getCacheDriver ()->save ( 'recenttracks', $response );
    }

}