<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Cross-Origin Resource Sharing (CORS) Configuration
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
 */
class Cors extends BaseConfig
{
    // ...

    public array $api = [
        'allowedOrigins'         => ['https://app.example.com'],
        'allowedOriginsPatterns' => [],
        'supportsCredentials'    => true,
        'allowedHeaders'         => ['Authorization', 'Content-Type'],
        'exposedHeaders'         => [],
        'allowedMethods'         => ['GET', 'POST', 'PUT', 'DELETE'],
        'maxAge'                 => 7200,
    ];
}
