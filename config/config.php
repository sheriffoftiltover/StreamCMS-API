<?php
declare(strict_types=1);
return [
    'cacheAnnotations' => true,
    // If TRUE, stores the annotation definitions in files /tmp/annotations/ (these need to be cleared if changes are made to annotations)
    'allowImpersonation' => false,
    // MUST BE OFF ON LIVE AT ALL TIMES. usage: /impersonate?user=Cene or /impersonate?userId=12
    'maintenance' => false,
    'profile' => [
        'nameChangeLimit' => 0
    ],
    'chat' => [
        'host' => $_SERVER['SERVER_NAME'],
        'port' => 9998,
        'backlog' => 150,
        'maxlines' => 150,
        'customemotes' => [
            'Hhhehhehe',
            'GameOfThrows',
            'WORTH',
            'FeedNathan',
            'Abathur',
            'LUL',
            'Heimerdonger',
            'SoSad',
            'DURRSTINY',
            'NoTears',
            'OverRustle',
            'DuckerZ',
            'Kappa',
            'Klappa',
            'DappaKappa',
            'BibleThump',
            'AngelThump',
            'FrankerZ',
            'BasedGod',
            'OhKrappa',
            'SoDoge',
            'WhoahDude',
            'DESBRO',
            'MotherFuckinGame',
            'DaFeels',
            'UWOTM8',
            'CallCatz',
            'CallChad',
            'DatGeoff',
            'Disgustiny',
            'DestiSenpaii',
            'KINGSLY',
            'Nappa',
            'DAFUK',
            'AYYYLMAO',
        ],
    ],
    'redis' => [
        'host' => 'localhost',
        'port' => 6379,
        'database' => 0,
        'scriptdir' => _BASEDIR . '/scripts/redis/',
    ],
    'curl' => [
        'verifypeer' => false,
        'timeout' => 30,
        'connecttimeout' => 5
    ],
    'rememberme' => [
        'cookieName' => 'rememberme'
    ],
    'authProfiles' => [
        'twitch',
        'google',
        'twitter',
        'reddit'
    ],
    'oauth' => [
        'callback' => '/%s',
        'providers' => [
            'google' => [
                'clientId' => '',
                'clientSecret' => ''
            ],
            'twitch' => [
                'clientId' => '',
                'clientSecret' => ''
            ],
            'twitter' => [
                'clientId' => '',
                'clientSecret' => '',
                'token' => '',
                'secret' => ''
            ],
            'reddit' => [
                'clientId' => '',
                'clientSecret' => '',
                'token' => '',
                'secret' => ''
            ]
        ]
    ],
    'regions' => [
        'Africa' => DateTimeZone::AFRICA,
        'America' => DateTimeZone::AMERICA,
        'Antarctica' => DateTimeZone::ANTARCTICA,
        'Asia' => DateTimeZone::ASIA,
        'Atlantic' => DateTimeZone::ATLANTIC,
        'Australia' => DateTimeZone::AUSTRALIA,
        'Europe' => DateTimeZone::EUROPE,
        'Indian' => DateTimeZone::INDIAN,
        'Pacific' => DateTimeZone::PACIFIC
    ],
    'cdn' => [
        'domain' => ''
    ],
    'cookie' => [
        'domain' => '',
        'name' => 'sid',
        'path' => '/'
    ],
    'tpl' => [
        'path' => _BASEDIR . '/lib/Resources/views/',
        'error.path' => _BASEDIR . '/',
    ],
    'geodata' => [
        'json' => _BASEDIR . '/lib/Resources/geo-ISO_3166-1-2.json'
    ],
    'log' => [
        'path' => _BASEDIR . '/log/'
    ],
    'cache' => [
        'path' => _BASEDIR . '/tmp/'
    ],
    'db' => [
        'driver' => 'pdo_mysql',
        'host' => '',
        'user' => '',
        'dbname' => '',
        'password' => '',
        'charset' => 'UTF8',
        'driverOptions' => [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8,time_zone = \'+0:00\'']
    ],
    'meta' => [
        'shortName' => 'Destiny',
        'title' => 'Destiny : Steven Bonnell II',
        'author' => 'Steven Bonnell II',
        'description' => 'Destiny.gg, Online streamer, primarily playing League of Legends, but I will often venture off into other topics, including but not limited to: philosophy, youtube videos, music and all sorts of wonderful pseudo-intellectualism.',
        'keywords' => 'Destiny.gg,Online,stream,game,pc,League of Legends',
        'video' => 'http://www-cdn.jtvnw.net/widgets/live_facebook_embed_player.swf?channel=destiny',
        'videoSecureUrl' => 'https://secure.jtvnw.net/widgets/live_facebook_embed_player.swf?channel=destiny'
    ],
    'paypal' => [
        'support_email' => 'support@destiny.gg',
        'email' => 'support@destiny.gg',
        'name' => 'Destiny.gg',
        'api' => [
            'endpoint' => '',
            'ipn' => ''
        ]
    ],
    'youtube' => [
        'apikey' => '',
        'playlistId' => '',
        'user' => ''
    ],
    'analytics' => [
        'account' => '',
        'domainName' => ''
    ],
    'googleads' => [
        '300x250' => [
            'google_ad_client' => '',
            'google_ad_slot' => '',
            'google_ad_width' => 300,
            'google_ad_height' => 250
        ]
    ],
    'calendar' => '',
    'lastfm' => [
        'apikey' => '',
        'user' => ''
    ],
    'twitch' => [
        'user' => '',
        'client_id' => '',
        'client_secret' => '',
        'broadcasterAuth' => false,
        'broadcaster' => [
            'user' => ''
        ]
    ],
    'twitter' => [
        'user' => '',
        'consumer_key' => '',
        'consumer_secret' => ''
    ],
    'subscriptionType' => 'destiny.gg',
    'commerce' => [
        'currencies' => [
            'USD' => [
                'code' => 'USD',
                'symbol' => '$'
            ]
        ],
        'reciever' => [
            'brandName' => 'Destiny.gg - Subscriptions'
        ],
        'receiver_email' => '',
        'currency' => 'USD',
        'subscriptions' => [
            '1-MONTH-SUB' => [
                'id' => '1-MONTH-SUB',
                'tier' => 1,
                'tierLabel' => 'Tier I',
                'itemLabel' => 'Tier 1 (1 month)',
                'agreement' => '$5.00 (per month) recurring subscription',
                'amount' => '5.00',
                'billingFrequency' => 1,
                'billingPeriod' => 'Month'
            ],
            '3-MONTH-SUB' => [
                'id' => '3-MONTH-SUB',
                'tier' => 1,
                'tierLabel' => 'Tier I',
                'itemLabel' => 'Tier 1 (3 month)',
                'agreement' => '$12.00 (per 3 months) recurring subscription',
                'amount' => '12.00',
                'billingFrequency' => 3,
                'billingPeriod' => 'Month'
            ],
            '1-MONTH-SUB2' => [
                'id' => '1-MONTH-SUB2',
                'tier' => 2,
                'tierLabel' => 'Tier II',
                'itemLabel' => 'Tier 2 (1 month)',
                'agreement' => '$10.00 (per month) recurring subscription',
                'amount' => '10.00',
                'billingFrequency' => 1,
                'billingPeriod' => 'Month'
            ],
            '3-MONTH-SUB2' => [
                'id' => '3-MONTH-SUB2',
                'tier' => 2,
                'tierLabel' => 'Tier II',
                'itemLabel' => 'Tier 2 (3 month)',
                'agreement' => '$24.00 (per 3 months) recurring subscription',
                'amount' => '24.00',
                'billingFrequency' => 3,
                'billingPeriod' => 'Month'
            ],
            '1-MONTH-SUB3' => [
                'id' => '1-MONTH-SUB3',
                'tier' => 3,
                'tierLabel' => 'Tier III',
                'itemLabel' => 'Tier 3 (1 month)',
                'agreement' => '$20.00 (per month) recurring subscription',
                'amount' => '20.00',
                'billingFrequency' => 1,
                'billingPeriod' => 'Month'
            ],
            '3-MONTH-SUB3' => [
                'id' => '3-MONTH-SUB3',
                'tier' => 3,
                'tierLabel' => 'Tier III',
                'itemLabel' => 'Tier 3 (3 month)',
                'agreement' => '$48.00 (per 3 months) recurring subscription',
                'amount' => '48.00',
                'billingFrequency' => 3,
                'billingPeriod' => 'Month'
            ],
            '1-MONTH-SUB4' => [
                'id' => '1-MONTH-SUB4',
                'tier' => 4,
                'tierLabel' => 'Tier IV',
                'itemLabel' => 'Tier 4 (1 month)',
                'agreement' => '$40.00 (per month) recurring subscription',
                'amount' => '40.00',
                'billingFrequency' => 1,
                'billingPeriod' => 'Month'
            ],
            '3-MONTH-SUB4' => [
                'id' => '3-MONTH-SUB4',
                'tier' => 4,
                'tierLabel' => 'Tier IV',
                'itemLabel' => 'Tier 4 (3 month)',
                'agreement' => '$96.00 (per 3 months) recurring subscription',
                'amount' => '96.00',
                'billingFrequency' => 3,
                'billingPeriod' => 'Month'
            ]
        ]
    ],
    'scheduler' => [
        'frequency' => 1,
        'period' => 'minute',
        'schedule' => [
            'SubscriptionExpire' => [
                'action' => 'SubscriptionExpire',
                'lastExecuted' => null,
                'frequency' => 30,
                'period' => 'minute',
                'executeOnNextRun' => false
            ],
            'LastFmFeed' => [
                'action' => 'LastFmFeed',
                'lastExecuted' => null,
                'frequency' => 1,
                'period' => 'minute',
                'executeOnNextRun' => true
            ],
            'YoutubeFeed' => [
                'action' => 'YoutubeFeed',
                'lastExecuted' => null,
                'frequency' => 30,
                'period' => 'minute',
                'executeOnNextRun' => true
            ],
            'BroadcastsFeed' => [
                'action' => 'BroadcastsFeed',
                'lastExecuted' => null,
                'frequency' => 30,
                'period' => 'minute',
                'executeOnNextRun' => true
            ],
            'TwitterFeed' => [
                'action' => 'TwitterFeed',
                'lastExecuted' => null,
                'frequency' => 30,
                'period' => 'minute',
                'executeOnNextRun' => true
            ],
            'BlogFeed' => [
                'action' => 'BlogFeed',
                'lastExecuted' => null,
                'frequency' => 60,
                'period' => 'minute',
                'executeOnNextRun' => true
            ],
            'StreamInfo' => [
                'action' => 'StreamInfo',
                'lastExecuted' => null,
                'frequency' => 1,
                'period' => 'minute',
                'executeOnNextRun' => true
            ],
            'RedditSubscribers' => [
                'action' => 'RedditSubscribers',
                'lastExecuted' => null,
                'frequency' => 1,
                'period' => 'hour',
                'executeOnNextRun' => true,
                'output' => ''
            ]
        ]
    ]
];
