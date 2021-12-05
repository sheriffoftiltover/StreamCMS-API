<?php

namespace Destiny\Tasks;

use Destiny\Blog\BlogApiService;
use Destiny\Common\Application;
use Psr\Log\LoggerInterface;

class BlogFeed
{

    public function execute(LoggerInterface $log)
    {
        $response = BlogApiService::instance()->getBlogPosts()->getResponse();
        if (!empty ($response))
            Application::instance()->getCacheDriver()->save('recentblog', $response);
    }

}