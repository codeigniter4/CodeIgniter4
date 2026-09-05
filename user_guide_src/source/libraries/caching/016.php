<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cache extends BaseConfig
{
    // ...

    public $redis = [
        'host'       => '127.0.0.1',
        'password'   => null,
        'port'       => 6379,
        'async'      => false, // specific to Predis and ignored by the native Redis extension
        'persistent' => false,
        'timeout'    => 0,
        'database'   => 0,
        // Connect through Redis Sentinel. When populated, `host`/`port` are
        // ignored by the Redis handler (phpredis) and the Predis handler uses
        // the Sentinel nodes to discover and follow the master.
        'sentinel' => [
            'service' => 'mymaster',
            'nodes'   => [
                ['host' => '127.0.0.1', 'port' => 26379],
                ['host' => 'sentinel2', 'port' => 26379],
                ['host' => 'sentinel3', 'port' => 26379],
            ],
            'timeout' => 0.5,
        ],
    ];

    // ...
}
