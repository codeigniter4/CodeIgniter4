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
		$this->setCookie();
	}

	//--------------------------------------------------------------------

	/**
	 * Takes care of setting the cookie on the client side.
	 * Extracted for testing reasons.
	 */
	protected function setCookie()
	{
		if (PHP_VERSION_ID < 70300)
		{
			// In PHP < 7.3.0, there is a "hacky" way to set the samesite parameter

			$sameSite = '';
			if (in_array(strtolower($this->cookieSameSite), ['none', 'lax', 'strict']))
			{
				$sameSite = '; samesite=' . $this->cookieSameSite;
			}

			$this->cookies[] = [
				$this->sessionCookieName,
				session_id(),
				(empty($this->sessionExpiration) ? 0 : time() + $this->sessionExpiration),
				$this->cookiePath . $sameSite,
				$this->cookieDomain,
				$this->cookieSecure,
				true,
			];
		}
		else
		{
			// PHP 7.3 adds another function signature allowing setting of samesite
			$params = [
				'lifetime' => $this->sessionExpiration,
				'path'     => $this->cookiePath,
				'domain'   => $this->cookieDomain,
				'secure'   => $this->cookieSecure,
				'httponly' => true,
			];

			if (in_array(strtolower($this->cookieSameSite), ['none', 'lax', 'strict']))
			{
				$params['samesite'] = $this->cookieSameSite;
			}

			$this->cookies[] = [
				$this->sessionCookieName,
				session_id(),
				$params,
			];
		}
	}

	//--------------------------------------------------------------------

	public function regenerate(bool $destroy = false)
	{
		$this->didRegenerate              = true;
		$_SESSION['__ci_last_regenerate'] = time();
	}

	//--------------------------------------------------------------------
}
