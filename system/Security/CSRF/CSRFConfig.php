<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Security\CSRF;

use Config\App;
use Config\Security as SecurityConfig;

use function config;

/**
 * Config for Cross Site Request Forgery protection
 */
class CSRFConfig
{
    /**
     * --------------------------------------------------------------------------
     * CSRF Token Name
     * --------------------------------------------------------------------------
     *
     * Token name for Cross Site Request Forgery protection.
     *
     * @var string
     */
    public $tokenName = 'csrf_test_name';

    /**
     * --------------------------------------------------------------------------
     * CSRF Header Name
     * --------------------------------------------------------------------------
     *
     * Header name for Cross Site Request Forgery protection.
     *
     * @var string
     */
    public $headerName = 'X-CSRF-TOKEN';

    /**
     * --------------------------------------------------------------------------
     * CSRF Cookie Name
     * --------------------------------------------------------------------------
     *
     * Cookie name for Cross Site Request Forgery protection.
     *
     * @var string
     */
    public $cookieName = 'csrf_cookie_name';

    /**
     * --------------------------------------------------------------------------
     * CSRF Expires
     * --------------------------------------------------------------------------
     *
     * Expiration time for Cross Site Request Forgery protection cookie.
     *
     * Defaults to two hours (in seconds).
     *
     * @var int
     */
    public $expires = 7200;

    /**
     * --------------------------------------------------------------------------
     * CSRF Regenerate
     * --------------------------------------------------------------------------
     *
     * Regenerate CSRF Token on every request.
     *
     * @var bool
     */
    public $regenerate = true;

    /**
     * --------------------------------------------------------------------------
     * CSRF Redirect
     * --------------------------------------------------------------------------
     *
     * Redirect to previous page with error on failure.
     *
     * @var bool
     */
    public $redirect = true;

    public function __construct(?SecurityConfig $securityConfig = null)
    {
        /** @var App $config */
        $config = config('App');

        // If `Config/Security.php` does not exist
        if ($securityConfig === null) {
            $this->tokenName  = $config->CSRFTokenName ?? $this->tokenName;
            $this->headerName = $config->CSRFHeaderName ?? $this->headerName;
            $this->cookieName = $config->CSRFCookieName ?? $this->cookieName;
            $this->expires    = $config->CSRFExpire ?? $this->expires;
            $this->regenerate = $config->CSRFRegenerate ?? $this->regenerate;
            $this->redirect   = $config->CSRFRedirect ?? $this->redirect;
        } else {
            foreach (get_object_vars($securityConfig) as $property => $value) {
                if (property_exists($this, $property)) {
                    $this->{$property} = $value;
                }
            }
        }
    }
}
