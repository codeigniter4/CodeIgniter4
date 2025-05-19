<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class CURLRequest extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * CURLRequest Share Connection
     * --------------------------------------------------------------------------
     *
     * Whether share connection between requests.
     *
     * @var list<int>
     *
     * @see https://www.php.net/manual/en/curl.constants.php#constant.curl-lock-data-connect
     */
    public array $shareConnection = [
        CURL_LOCK_DATA_CONNECT,
        CURL_LOCK_DATA_DNS,
    ];

    // ...
}
