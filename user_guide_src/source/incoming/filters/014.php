<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    // ...
    public array $required = [
        'before' => [
            'forcehttps', // Force Global Secure Requests
            'requestid',  // Request ID for each request
            'pagecache',  // Web Page Caching
        ],
        'after' => [
            'pagecache',   // Web Page Caching
            'requestid',   // Request ID for each request
            'performance', // Performance Metrics
            'toolbar',     // Debug Toolbar
        ],
    ];

    // ...
}
