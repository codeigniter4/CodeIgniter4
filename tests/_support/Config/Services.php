<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Config;

use CodeIgniter\Exceptions\RuntimeException;
use CodeIgniter\HTTP\SiteURIFactory;
use CodeIgniter\HTTP\URI;
use Config\App;
use Config\Services as BaseServices;

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
     * @param string|null $uri The URI string
     */
    public static function uri(?string $uri = null, bool $getShared = true): URI
    {
        // Intercept our test case
        if ($uri === 'testCanReplaceFrameworkServices') {
            throw new RuntimeException('Service originated from ' . static::class);
        }

        if ($getShared) {
            return static::getSharedInstance('uri', $uri);
        }

        if ($uri === null) {
            $appConfig = config(App::class);
            $factory   = new SiteURIFactory($appConfig, Services::superglobals());

            return $factory->createFromGlobals();
        }

        return new URI($uri);
    }
}
