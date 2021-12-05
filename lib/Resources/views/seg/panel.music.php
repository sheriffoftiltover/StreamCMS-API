<?php
declare(strict_types=1);

use Destiny\Common\Config;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;

?>
<section class="container">
    <div class="content content-dark clearfix">

        <div id="stream-twitter" class="stream">
            <h3 class="title">
                <span>Tweets</span>
                <a href="https://twitter.com/<?= Config::$a['twitter']['user'] ?>/">twitter.com</a>
            </h3>
            <div class="entries">
                <?php if (!empty($model->tweets)): ?>
                    <?php foreach ($model->tweets as $tweetIndex => $tweet): ?>
                        <?php if ($tweetIndex == 3) {
                            break;
                        } ?>
                        <div class="media">
                            <div class="media-body">
                                <div class="media-heading">
                                    <a target="_blank"
                                       href="https://twitter.com/<?= $tweet['user']['screen_name'] ?>/status/<?= $tweet['id_str'] ?>">
                                        <span class="glyphicon glyphicon-share"></span>
                                    </a>
                                    <?= $tweet['html'] ?>
                                </div>
                                <?= Tpl::fromNow(Date::getDateTime($tweet['created_at']), Date::FORMAT) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="loading">Loading tweets ...</p>
                <?php endif; ?>
            </div>
        </div>

        <div id="stream-lastfm" class="stream">
            <h3 class="title">
                <span>Music</span>
                <a href="http://www.last.fm/user/<?= Config::$a['lastfm']['user'] ?>">last.fm</a>
            </h3>
            <div class="entries">
                <?php if (!empty($model->music) && isset($model->music['recenttracks']['track']) && !empty($model->music['recenttracks']['track'])): ?>
                    <?php foreach ($model->music['recenttracks']['track'] as $trackIndex => $track): ?>
                        <?php if ($trackIndex == 3) {
                            break;
                        } ?>
                        <div class="media">
                            <a class="pull-left cover-image" href="<?= $track['url'] ?>"><img class="media-object"
                                                                                              src="<?= Config::cdn() ?>/web/img/64x64.gif"
                                                                                              data-src="<?= $track['image'][1]['#text'] ?>"></a>
                            <div class="media-body">
                                <div class="media-heading trackname">
                                    <a href="<?= $track['url'] ?>"><?= Tpl::out($track['name']) ?></a>
                                </div>
                                <div class="artist"><?= Tpl::out($track['artist']['#text']) ?></div>
                                <div class="details">
                                    <?php if ($track['date_str'] != ''): ?>
                                        <span class="pull-right"><?= Tpl::fromNow(Date::getDateTime($track['date_str']), Date::FORMAT) ?></span>
                                    <?php endif; ?>
                                    <?php if ($trackIndex == 0 && $track['date_str'] == ''): ?>
                                        <span class="pull-right"><time>now playing</time></span>
                                    <?php endif; ?>
                                    <small class="album subtle"><?= Tpl::out($track['album']['#text']) ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="loading">Loading music ...</p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>