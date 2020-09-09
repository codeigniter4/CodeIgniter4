<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Session extends BaseConfig
{
	/**
	 * --------------------------------------------------------------------------
	 * Session Driver
	 * --------------------------------------------------------------------------
	 *
	 * The session storage driver to use:
	 * - `CodeIgniter\Session\Handlers\FileHandler`
	 * - `CodeIgniter\Session\Handlers\DatabaseHandler`
	 * - `CodeIgniter\Session\Handlers\MemcachedHandler`
	 * - `CodeIgniter\Session\Handlers\RedisHandler`
	 *
	 * @var string
	 */
	public $sessionDriver = 'CodeIgniter\Session\Handlers\FileHandler';

	/**
	 * --------------------------------------------------------------------------
	 * Session Cookie Name
	 * --------------------------------------------------------------------------
	 *
	 * The session cookie name, must contain only [0-9a-z_-] characters
	 *
	 * @var string
	 */
	public $sessionCookieName = 'ci_session';

	/**
	 * --------------------------------------------------------------------------
	 * Session Expiration
	 * --------------------------------------------------------------------------
	 *
	 * The number of seconds you want the session to last.
	 * Setting to 0 (zero) means expire when the browser is closed.
	 *
	 * @var integer
	 */
	public $sessionExpiration = 7200;

	/**
	 * --------------------------------------------------------------------------
	 * Session Save Path
	 * --------------------------------------------------------------------------
	 *
	 * The location to save sessions to and is driver dependent.
	 *
	 * For the 'files' driver, it's a path to a writable directory.
   *
	 * WARNING: Only absolute paths are supported!
	 *
	 * For the 'database' driver, it's a table name.
	 * Please read up the manual for the format with other session drivers.
	 *
	 * IMPORTANT: You are REQUIRED to set a valid save path!
	 *
	 * @var string
	 */
	public $sessionSavePath = WRITEPATH . 'session';

	/**
	 * --------------------------------------------------------------------------
	 * Session Match IP
	 * --------------------------------------------------------------------------
	 *
	 * Whether to match the user's IP address when reading the session data.
	 *
	 * WARNING: If you're using the database driver, don't forget to update
	 *          your session table's PRIMARY KEY when changing this setting.
	 *
	 * @var boolean
	 */
	public $sessionMatchIP = false;

	/**
	 * --------------------------------------------------------------------------
	 * Session Time to Update
	 * --------------------------------------------------------------------------
	 *
	 * How many seconds between CI regenerating the session ID.
	 *
	 * @var integer
	 */
	public $sessionTimeToUpdate = 300;

	/**
	 * --------------------------------------------------------------------------
	 * Session Regenerate Destroy
	 * --------------------------------------------------------------------------
	 *
	 * Whether to destroy session data associated with the old session ID
	 * when auto-regenerating the session ID. When set to FALSE, the data
	 * will be later deleted by the garbage collector.
	 *
	 * @var boolean
	 */
	public $sessionRegenerateDestroy = false;

	/**
	 * --------------------------------------------------------------------------
	 * Cookie Prefix
	 * --------------------------------------------------------------------------
	 *
	 * Set a cookie name prefix if you need to avoid collisions.
	 *
	 * @var string
	 */
	public $cookiePrefix = '';

	/**
	 * --------------------------------------------------------------------------
	 * Cookie Domain
	 * --------------------------------------------------------------------------
	 *
	 * Set to `.your-domain.com` for site-wide cookies.
	 *
	 * @var string
	 */
	public $cookieDomain = '';

	/**
	 * --------------------------------------------------------------------------
	 * Cookie Path
	 * --------------------------------------------------------------------------
	 *
	 * Typically will be a forward slash.
	 *
	 * @var string
	 */
	public $cookiePath = '/';

	/**
	 * --------------------------------------------------------------------------
	 * Cookie Secure
	 * --------------------------------------------------------------------------
	 *
	 * Cookie will only be set if a secure HTTPS connection exists.
	 *
	 * @var boolean
	 */
	public $cookieSecure = false;

	/**
	 * --------------------------------------------------------------------------
	 * Cookie HTTP Only
	 * --------------------------------------------------------------------------
	 *
	 * Cookie will only be accessible via HTTP(S) (no JavaScript).
	 *
	 * @var boolean
	 */
	public $cookieHTTPOnly = false;

	/**
	 * --------------------------------------------------------------------------
	 * Cookie SameSite
	 * --------------------------------------------------------------------------
	 *
	 * Configure cookie SameSite setting. Allowed values are:
	 * - None
	 * - Lax
	 * - Strict
	 * - ''
	 *
	 * Defaults to `Lax` for compatibility with modern browsers. Setting `''`
	 * (empty string) means no SameSite attribute will be set on cookies. If
	 * set to `None`, `$cookieSecure` must also be set.
	 *
	 * @var string
	 */
	public $cookieSameSite = 'Lax';
}
