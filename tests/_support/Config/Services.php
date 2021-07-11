<?php

namespace Tests\Support\Config;

use CodeIgniter\HTTP\URI;
use Config\Services as BaseServices;
use RuntimeException;

/**
 * Services Class
 *
 * Provides a replacement uri Service
 * to demonstrate overriding core services.
 */
class Services extends BaseServices
{
    /**
     * The URI class provides a way to model and manipulate URIs.
     *
     * @param string $uri
     * @param bool   $getShared
     *
     * @return URI
     */
    public static function uri(?string $uri = null, bool $getShared = true)
    {
        // Intercept our test case
        if ($uri === 'testCanReplaceFrameworkServices') {
            throw new RuntimeException('Service originated from ' . static::class);
        }

        if ($getShared) {
            return static::getSharedInstance('uri', $uri);
        }

        return new URI($uri);
    }
}
