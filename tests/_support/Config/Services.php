<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

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
