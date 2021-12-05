<?php
declare(strict_types=1);

namespace Destiny\Tasks;

use Destiny\Common\Application;
use Destiny\Youtube\YoutubeApiService;
use Psr\Log\LoggerInterface;

class YoutubeFeed
{

    public function execute(LoggerInterface $log)
    {
        $app = Application::instance();
        $response = YoutubeApiService::instance()->getYoutubePlaylist()->getResponse();
        if (!empty ($response))
            $app->getCacheDriver()->save('youtubeplaylist', $response);
    }

}