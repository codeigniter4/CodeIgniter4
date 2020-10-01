<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Security extends BaseConfig
{
	/**
	 * --------------------------------------------------------------------------
	 * CSRF Token Name
	 * --------------------------------------------------------------------------
	 *
	 * The token name.
	 *
	 * @var string
	 */
	public $tokenName = 'csrf_test_name';

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Header Name
	 * --------------------------------------------------------------------------
	 *
	 * The header name.
	 *
	 * @var string
	 */
	public $headerName = 'X-CSRF-TOKEN';

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Cookie Name
	 * --------------------------------------------------------------------------
	 *
	 * The cookie name.
	 *
	 * @var string
	 */
	public $cookieName = 'csrf_cookie_name';

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Expire
	 * --------------------------------------------------------------------------
	 *
	 * The number in seconds the token should expire.
	 *
	 * @var integer
	 */
	public $expire = 7200;

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Regenerate
	 * --------------------------------------------------------------------------
	 *
	 * Regenerate token on every submission?
	 *
	 * @var boolean
	 */
	public $regenerate = true;

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Redirect
	 * --------------------------------------------------------------------------
	 *
	 * Redirect to previous page with error on failure?
	 *
	 * @var boolean
	 */
	public $redirect = true;

	/**
	 * --------------------------------------------------------------------------
	 * CSRF SameSite
	 * --------------------------------------------------------------------------
	 *
	 * Setting for CSRF SameSite cookie token. Allowed values are:
	 * - None
	 * - Lax
	 * - Strict
	 * - ''
	 *
	 * Defaults to `Lax` as recommended in this link:
	 *
	 * @see https://portswigger.net/web-security/csrf/samesite-cookies
	 *
	 * @var string
	 */
	public $samesite = 'Lax';
  }
