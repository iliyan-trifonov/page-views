<?php

$pageViewsConfig = [
    'default' => 'redis',
    'servers' => [
        'memcached' => [
            'host'        => '127.0.0.1',
            'port'        => '11211',
            'keyprefix'   => 'pageviews_stats',
            'time'        => 86400, //24h
        ],
        'redis' => [
            'host'      => '127.0.0.1',
            'port'      => '6379',
            'hash'      => 'pageviews_stats',
            'time'      => 86400, //24h
        ],
    ],
];
