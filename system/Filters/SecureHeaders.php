<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Add Common Security Headers
 */
class SecureHeaders implements FilterInterface
{
    /**
     * @var array<string, string>
     */
    protected $headers = [
        // https://owasp.org/www-project-secure-headers/#x-frame-options
        'X-Frame-Options' => 'SAMEORIGIN',

        // https://owasp.org/www-project-secure-headers/#x-content-type-options
        'X-Content-Type-Options' => 'nosniff',

        // https://docs.microsoft.com/en-us/previous-versions/windows/internet-explorer/ie-developer/compatibility/jj542450(v=vs.85)#the-noopen-directive
        'X-Download-Options' => 'noopen',

        // https://owasp.org/www-project-secure-headers/#x-permitted-cross-domain-policies
        'X-Permitted-Cross-Domain-Policies' => 'none',

        // https://owasp.org/www-project-secure-headers/#referrer-policy
        'Referrer-Policy' => 'same-origin',

        // https://owasp.org/www-project-secure-headers/#x-xss-protection
        // If you do not need to support legacy browsers, it is recommended that you use
        // Content-Security-Policy without allowing unsafe-inline scripts instead.
        // 'X-XSS-Protection' => '1; mode=block',
    ];

    /**
     * We don't have anything to do here.
     *
     * @param array|null $arguments
     *
     * @return void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
    }

    /**
     * Add security headers.
     *
     * @param array|null $arguments
     *
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        foreach ($this->headers as $header => $value) {
            $response->setHeader($header, $value);
        }
    }
}
