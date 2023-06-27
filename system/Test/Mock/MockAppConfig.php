<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Mock;

use Config\App;

class MockAppConfig extends App
{
    public string $baseURL         = 'http://example.com/';
    public string $uriProtocol     = 'REQUEST_URI';
    public array $proxyIPs         = [];
    public bool $CSPEnabled        = false;
    public string $defaultLocale   = 'en';
    public bool $negotiateLocale   = false;
    public array $supportedLocales = [
        'en',
        'es',
    ];
}
