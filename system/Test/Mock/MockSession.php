<?php namespace CodeIgniter\Test\Mock;

use CodeIgniter\Session\Session;

/**
 * Class MockSession
 *
 * Provides a safe way to test the Session class itself,
 * that doesn't interact with the session or cookies at all.
 */
class MockSession extends Session
{
	/**
	 * Holds our "cookie" data.
	 *
	 * @var array
	 */
	public $cookies = [];

	public $didRegenerate = false;

	//--------------------------------------------------------------------

	/**
	 * Sets the driver as the session handler in PHP.
	 * Extracted for easier testing.
	 */
	protected function setSaveHandler()
	{
		//        session_set_save_handler($this->driver, true);
	}

	//--------------------------------------------------------------------

	/**
	 * Starts the session.
	 * Extracted for testing reasons.
	 */
	protected function startSession()
	{
		//        session_start();
	}

	//--------------------------------------------------------------------

	/**
	 * Takes care of setting the cookie on the client side.
	 * Extracted for testing reasons.
	 */
	protected function setCookie()
	{
		$this->cookies[] = [
			$this->sessionCookieName,
			session_id(),
			(empty($this->sessionExpiration) ? 0 : time() + $this->sessionExpiration),
			$this->cookiePath,
			$this->cookieDomain,
			$this->cookieSecure,
			true,
		];
	}

	//--------------------------------------------------------------------

	public function regenerate(bool $destroy = false)
	{
		$this->didRegenerate              = true;
		$_SESSION['__ci_last_regenerate'] = time();
	}

	//--------------------------------------------------------------------
}
