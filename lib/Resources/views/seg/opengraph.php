<?php

use Destiny\Common\Config;
use Destiny\Common\Utils\Http;

$arr = ['og:site_name' => Config::$a ['meta'] ['shortName'], 'og:title' => Config::$a ['meta'] ['title'], 'og:description' => Config::$a['meta']['description'], 'og:image' => Config::cdn() . '/web/img/destinygg.png', 'og:url' => Http::getBaseUrl(), 'og:type' => 'video.other', 'og:video' => Config::$a['meta']['video'], 'og:video:secure_url' => Config::$a['meta']['videoSecureUrl'], 'og:video:type' => 'application/x-shockwave-flash', 'og:video:height' => '260', 'og:video:width' => '340',];
foreach ($arr as $k => $v) {
    echo '<meta property="' . $k . '" content="' . $v . '" />' . PHP_EOL;
}
?>