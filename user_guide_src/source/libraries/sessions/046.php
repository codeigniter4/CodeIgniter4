<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Session\Handlers\RedisHandler;

class Session extends BaseConfig
{
    // ...

    public string $driver = RedisHandler::class;

    // When `$sentinel` is populated, `$savePath` is ignored and the RedisHandler
    // connects through Redis Sentinel instead of a single fixed host.
    public array $sentinel = [
        'service' => 'mymaster',
        'nodes'   => [
            ['host' => '127.0.0.1', 'port' => 26379],
            ['host' => 'sentinel2', 'port' => 26379],
            ['host' => 'sentinel3', 'port' => 26379],
        ],
        'timeout'    => 0.5,
        'persistent' => false,
        // 'password' => null,
        // 'database' => 0,
    ];

    // ...
}
