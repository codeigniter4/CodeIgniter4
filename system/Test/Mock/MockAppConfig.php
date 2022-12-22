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
    public string $cookiePrefix    = '';
    public string $cookieDomain    = '';
    public string $cookiePath      = '/';
    public bool $cookieSecure      = false;
    public bool $cookieHTTPOnly    = false;
    public ?string $cookieSameSite = 'Lax';
    public array $proxyIPs         = [];
    public string $CSRFTokenName   = 'csrf_test_name';
    public string $CSRFHeaderName  = 'X-CSRF-TOKEN';
    public string $CSRFCookieName  = 'csrf_cookie_name';
    public int $CSRFExpire         = 7200;
    public bool $CSRFRegenerate    = true;
    public array $CSRFExcludeURIs  = ['http://example.com'];
    public bool $CSRFRedirect      = false;
    public string $CSRFSameSite    = 'Lax';
    public bool $CSPEnabled        = false;
    public string $defaultLocale   = 'en';
    public bool $negotiateLocale   = false;
    public array $supportedLocales = [
        'en',
        'es',
    ];
}
