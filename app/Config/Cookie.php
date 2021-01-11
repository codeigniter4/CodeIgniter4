<?php

namespace Config;

class Cookie
{
	/**
	 * --------------------------------------------------------------------------
	 * Cookie Prefix
	 * --------------------------------------------------------------------------
	 *
	 * Set a cookie name prefix to avoid collisions.
	 *
	 * @var string
	 */
	public $prefix = '';

	/**
	 * --------------------------------------------------------------------------
	 * Cookie Expires
	 * --------------------------------------------------------------------------
	 *
	 * Default expires timestamp for cookie.
	 *
	 * @var integer
	 */
	public $expires = 0;

	/**
	 * --------------------------------------------------------------------------
	 * Cookie Path
	 * --------------------------------------------------------------------------
	 *
	 * Typically will be a forward slash.
	 *
	 * @var string
	 */
	public $path = '/';

	/**
	 * --------------------------------------------------------------------------
	 * Cookie Domain
	 * --------------------------------------------------------------------------
	 *
	 * Set to `.example-domain.com` for site-wide cookies.
	 *
	 * @var string
	 */
	public $domain = '';

	/**
	 * --------------------------------------------------------------------------
	 * Cookie Secure
	 * --------------------------------------------------------------------------
	 *
	 * Transmit the cookie over a secure HTTPS connection only.
	 *
	 * @var boolean
	 */
	public $secure = false;

	/**
	 * --------------------------------------------------------------------------
	 * Cookie HTTP Only
	 * --------------------------------------------------------------------------
	 *
	 * Make the cookie accessible only through the HTTP protocol (no JavaScript)
	 *
	 * @var boolean
	 */
	public $httponly = false;

	/**
	 * --------------------------------------------------------------------------
	 * Cookie SameSite
	 * --------------------------------------------------------------------------
	 *
	 * Setting for cookie samesite.
	 *
	 * Allowed values are: [None - Lax - Strict].
	 *
	 * Default: `Lax` as recommended in this link:
	 * @see https://portswigger.net/web-security/csrf/samesite-cookies
	 *
	 * @var string
	 */
	public $samesite = 'Lax';
}
