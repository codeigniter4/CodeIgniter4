<?php namespace CodeIgniter\Session\Handlers;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Log\LoggerAwareTrait;

abstract class BaseHandler implements \SessionHandlerInterface
{
	use LoggerAwareTrait;

	/**
	 * The Data fingerprint.
	 *
	 * @var bool
	 */
	protected $fingerprint;

	/**
	 * Lock placeholder.
	 *
	 * @var mixed
	 */
	protected $lock = false;

	protected $cookiePrefix = '';

	protected $cookieDomain = '';

	protected $cookiePath = '/';

	protected $cookieSecure = false;

	protected $cookieName;

	protected $matchIP = false;

	protected $sessionID;

	//--------------------------------------------------------------------

	public function __construct(BaseConfig $config)
	{
		$this->cookiePrefix = $config->cookiePrefix;
		$this->cookieDomain = $config->cookoieDomain;
		$this->cookiePath   = $config->cookiePath;
		$this->cookieSecure = $config->cookieSecure;
		$this->cookieName   = $config->sessionCookieName;
		$this->matchIP      = $config->sessionMatchIP;
	}

	//--------------------------------------------------------------------

	/**
	 * Internal method to force removal of a cookie by the client
	 * when session_destroy() is called.
	 *
	 * @return bool
	 */
	protected function destroyCookie(): bool
	{
	    return setcookie(
		    $this->cookieName,
		    null,
		    1,
		    $this->cookiePath,
		    $this->cookieDomain,
		    $this->cookieSecure,
		    true
	    );
	}

	//--------------------------------------------------------------------

	/**
	 * A dummy method allowing drivers with no locking functionality
	 * (databases other than PostgreSQL and MySQL) to act as if they
	 * do acquire a lock.
	 *
	 * @param string $session_id
	 *
	 * @return bool
	 */
	protected function lockSession(string $session_id): bool
	{
		$this->lock = true;
		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Releases the lock, if any.
	 *
	 * @return bool
	 */
	protected function releaseLock(): bool
	{
		$this->lock = false;

		return true;
	}

	//--------------------------------------------------------------------

}
