<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    // ...
    public array $required = [
        'before' => [
            'requestid',  // Request ID for each request
            'forcehttps', // Force Global Secure Requests
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
