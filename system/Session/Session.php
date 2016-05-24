<?php namespace CodeIgniter\Session;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

use CodeIgniter\Log\LoggerAwareTrait;

/**
 * Class Session
 */
class Session implements SessionInterface
{
	use LoggerAwareTrait;

	/**
	 * Userdata array
	 *
	 * Just a reference to $_SESSION, for BC purposes.
	 */
	protected $userdata;

	/**
	 * Instance of the driver to use.
	 *
	 * @var HandlerInterface
	 */
	protected $driver;

	protected $sessionDriverName;

	protected $sessionCookieName = 'ci_session';

	protected $sessionExpiration = 7200;

	protected $sessionSavePath = null;

	protected $sessionMatchIP = false;

	protected $sessionTimeToUpdate = 300;

	protected $sessionRegenerateDestroy = false;

	protected $cookiePrefix = '';

	protected $cookieDomain = '';

	protected $cookiePath = '/';

	protected $cookieSecure = false;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	//--------------------------------------------------------------------

	public function __construct(\SessionHandlerInterface $driver, $config)
	{
		$this->driver = $driver;

		$this->sessionDriverName        = $config->sessionDriver;
		$this->sessionCookieName        = $config->sessionCookieName;
		$this->sessionExpiration        = $config->sessionExpiration;
		$this->sessionSavePath          = $config->sessionSavePath;
		$this->sessionMatchIP           = $config->sessionMatchIP;
		$this->sessionTimeToUpdate      = $config->sessionTimeToUpdate;
		$this->sessionRegenerateDestroy = $config->sessionRegenerateDestroy;

		$this->cookiePrefix = $config->cookiePrefix;
		$this->cookieDomain = $config->cookieDomain;
		$this->cookiePath   = $config->cookiePath;
		$this->cookieSecure = $config->cookieSecure;
	}

	//--------------------------------------------------------------------

	public function initialize()
	{
		if (is_cli())
		{
			$this->logger->debug('Session: Initialization under CLI aborted.');

			return;
		}
		else if ((bool)ini_get('session.auto_start'))
		{
			$this->logger->error('Session: session.auto_start is enabled in php.ini. Aborting.');

			return;
		}

		if ( ! $this->driver instanceof \SessionHandlerInterface)
		{
			$this->logger->error("Session: Handler '".$this->driver.
			                     "' doesn't implement SessionHandlerInterface. Aborting.");
		}

		$this->configure();

		session_set_save_handler($this->driver, true);

		// Sanitize the cookie, because apparently PHP doesn't do that for userspace handlers
		if (isset($_COOKIE[$this->sessionCookieName])
		    && (
			    ! is_string($_COOKIE[$this->sessionCookieName])
			    || ! preg_match('/^[0-9a-f]{40}$/', $_COOKIE[$this->sessionCookieName])
		    )
		)
		{
			unset($_COOKIE[$this->sessionCookieName]);
		}

		session_start();

		// Is session ID auto-regeneration configured? (ignoring ajax requests)
		if ((empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
		     strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest')
		    && ($regenerate_time = $this->sessionTimeToUpdate) > 0
		)
		{
			if ( ! isset($_SESSION['__ci_last_regenerate']))
			{
				$_SESSION['__ci_last_regenerate'] = time();
			}
			elseif ($_SESSION['__ci_last_regenerate'] < (time() - $regenerate_time))
			{
				$this->regenerate((bool)$this->sessionRegenerateDestroy);
			}
		}
		// Another work-around ... PHP doesn't seem to send the session cookie
		// unless it is being currently created or regenerated
		elseif (isset($_COOKIE[$this->sessionCookieName]) && $_COOKIE[$this->sessionCookieName] === session_id())
		{
			setcookie(
				$this->sessionCookieName,
				session_id(),
				(empty($this->sessionExpiration) ? 0 : time() + $this->sessionExpiration),
				$this->cookiePath,
				$this->cookieDomain,
				$this->cookieSecure,
				true
			);
		}

		$this->initVars();

		$this->logger->info("Session: Class initialized using '".$this->sessionDriverName."' driver.");
	}

	//--------------------------------------------------------------------

	/**
	 * Configuration
	 *
	 * Handle input binds and configuration defaults.
	 */
	protected function configure()
	{
		if (empty($this->sessionCookieName))
		{
			$this->sessionCookieName = ini_get('session.name');
		}
		else
		{
			ini_set('session.name', $this->sessionCookieName);
		}

		session_set_cookie_params(
			$this->sessionExpiration,
			$this->cookiePath,
			$this->cookieDomain,
			$this->cookieSecure,
			true // HTTP only; Yes, this is intentional and not configurable for security reasons.
		);

		if (empty($this->sessionExpiration))
		{
			$this->sessionExpiration = (int)ini_get('session.gc_maxlifetime');
		}
		else
		{
			ini_set('session.gc_maxlifetime', (int)$this->sessionExpiration);
		}

		// Security is king
		ini_set('session.use_trans_sid', 0);
		ini_set('session.use_strict_mode', 1);
		ini_set('session.use_cookies', 1);
		ini_set('session.use_only_cookies', 1);
		ini_set('session.hash_function', 1);
		ini_set('session.hash_bits_per_character', 4);
	}

	//--------------------------------------------------------------------

	/**
	 * Handle temporary variables
	 *
	 * Clears old "flash" data, marks the new one for deletion and handles
	 * "temp" data deletion.
	 */
	protected function initVars()
	{
		if ( ! empty($_SESSION['__ci_vars']))
		{
			$current_time = time();

			foreach ($_SESSION['__ci_vars'] as $key => &$value)
			{
				if ($value === 'new')
				{
					$_SESSION['__ci_vars'][$key] = 'old';
				}
				// Hacky, but 'old' will (implicitly) always be less than time() ;)
				// DO NOT move this above the 'new' check!
				elseif ($value < $current_time)
				{
					unset($_SESSION[$key], $_SESSION['__ci_vars'][$key]);
				}
			}

			if (empty($_SESSION['__ci_vars']))
			{
				unset($_SESSION['__ci_vars']);
			}
		}

		$this->userdata =& $_SESSION;
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Session Utility Methods
	//--------------------------------------------------------------------

	/**
	 * Regenerates the session ID.
	 *
	 * @param bool $destroy Should old session data be destroyed?
	 */
	public function regenerate($destroy = false)
	{
		$_SESSION['__ci_last_regenerate'] = time();
		session_regenerate_id($destroy);
	}

	//--------------------------------------------------------------------

	/**
	 * Destroys the current session.
	 */
	public function destroy()
	{
	    session_destroy();
	}

	//--------------------------------------------------------------------


	//--------------------------------------------------------------------
	// Basic Setters and Getters
	//--------------------------------------------------------------------

	/**
	 * Sets user data into the session.
	 *
	 * @param      $data
	 * @param null $value
	 */
	public function set($data, $value = null)
	{
		if (is_array($data))
		{
			foreach ($data as $key => &$value)
			{
				$_SESSION[$key] = $value;
			}

			return;
		}

		$_SESSION[$data] = $value;
	}

	//--------------------------------------------------------------------

	/**
	 * Get any user data that has been set in the session.
	 *
	 * Replaces the legacy method $session->userdata();
	 *
	 * @param null $key
	 *
	 * @return array|null
	 */
	public function get($key = null)
	{
		if (isset($key))
		{
			return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
		}
		elseif (empty($_SESSION))
		{
			return [];
		}

		$userdata = [];
		$_exclude = array_merge(
			['__ci_vars'],
			$this->getFlashKeys(),
			$this->getTempKeys()
		);

		foreach (array_keys($_SESSION) as $key)
		{
			if ( ! in_array($key, $_exclude, true))
			{
				$userdata[$key] = $_SESSION[$key];
			}
		}

		return $userdata;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns whether an index exists in the session array.
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public function has($key)
	{
		return isset($_SESSION[$key]);
	}

	//--------------------------------------------------------------------

	/**
	 * Unsets one or more bits of session data.
	 *
	 * @param $key
	 */
	public function unset($key)
	{
		if (is_array($key))
		{
			foreach ($key as $k)
			{
				unset($_SESSION[$k]);
			}

			return;
		}

		unset($_SESSION[$key]);
	}

	//--------------------------------------------------------------------

	/**
	 * Magic method to set variables in the session by simply calling
	 *  $session->foo = bar;
	 *
	 * @param $key
	 * @param $value
	 */
	public function __set($key, $value)
	{
		$_SESSION[$key] = $value;
	}

	//--------------------------------------------------------------------

	/**
	 * Magic method to get session variables by simply calling
	 *  $foo = $session->foo;
	 *
	 * @param $key
	 *
	 * @return null|string
	 */
	public function __get($key)
	{
		// Note: Keep this order the same, just in case somebody wants to
		//       use 'session_id' as a session data key, for whatever reason
		if (isset($_SESSION[$key]))
		{
			return $_SESSION[$key];
		}
		else if ($key === 'session_id')
		{
			return session_id();
		}

		return null;
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Flash Data Methods
	//--------------------------------------------------------------------

	/**
	 * Sets data into the session that will only last for a single request.
	 * Perfect for use with single-use status update messages.
	 *
	 * @param      $data
	 * @param null $value
	 */
	public function setFlashdata($data, $value = null)
	{
		$this->set($data, $value);
		$this->markAsFlashdata(is_array($data) ? array_keys($data) : $data);
	}

	//--------------------------------------------------------------------

	/**
	 * Grabs one or more items of flash data from the session.
	 *
	 * @param null $key
	 *
	 * @return array|null
	 */
	public function getFlashdata($key = null)
	{
		if (isset($key))
		{
			return (isset($_SESSION['__ci_vars'], $_SESSION['__ci_vars'][$key], $_SESSION[$key]) &&
			        ! is_int($_SESSION['__ci_vars'][$key]))
				? $_SESSION[$key]
				: null;
		}

		$flashdata = [];

		if ( ! empty($_SESSION['__ci_vars']))
		{
			foreach ($_SESSION['__ci_vars'] as $key => &$value)
			{
				is_int($value) OR $flashdata[$key] = $_SESSION[$key];
			}
		}

		return $flashdata;
	}

	//--------------------------------------------------------------------

	/**
	 * Keeps a single piece of flash data alive for one more request.
	 *
	 * @param $key
	 *
	 * @return $this
	 */
	public function keepFlashdata($key)
	{
		$this->markAsFlashdata($key);
	}

	//--------------------------------------------------------------------

	/**
	 * @param $key
	 *
	 * @return bool
	 */
	public function markAsFlashdata($key)
	{
		if (is_array($key))
		{
			for ($i = 0, $c = count($key); $i < $c; $i++)
			{
				if ( ! isset($_SESSION[$key[$i]]))
				{
					return false;
				}
			}

			$new = array_fill_keys($key, 'new');

			$_SESSION['__ci_vars'] = isset($_SESSION['__ci_vars'])
				? array_merge($_SESSION['__ci_vars'], $new)
				: $new;

			return true;
		}

		if ( ! isset($_SESSION[$key]))
		{
			return false;
		}

		$_SESSION['__ci_vars'][$key] = 'new';

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Unmark data in the session as flashdata.
	 *
	 * @param mixed $key
	 */
	public function unmarkFlashdata($key)
	{
		if (empty($_SESSION['__ci_vars']))
		{
			return;
		}

		is_array($key) OR $key = [$key];

		foreach ($key as $k)
		{
			if (isset($_SESSION['__ci_vars'][$k]) && ! is_int($_SESSION['__ci_vars'][$k]))
			{
				unset($_SESSION['__ci_vars'][$k]);
			}
		}

		if (empty($_SESSION['__ci_vars']))
		{
			unset($_SESSION['__ci_vars']);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Grabs all of the keys for session data marked as flashdata.
	 *
	 * @return array
	 */
	public function getFlashKeys()
	{
		if ( ! isset($_SESSION['__ci_vars']))
		{
			return [];
		}

		$keys = [];
		foreach (array_keys($_SESSION['__ci_vars']) as $key)
		{
			is_int($_SESSION['__ci_vars'][$key]) OR $keys[] = $key;
		}

		return $keys;
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Temp Data Methods
	//--------------------------------------------------------------------

	/**
	 * Sets new data into the session, and marks it as temporary data
	 * with a set lifespan.
	 *
	 * @param      $data    Session data key or associative array of items
	 * @param null $value   Value to store
	 * @param int  $ttl     Time-to-live in seconds
	 */
	public function setTempdata($data, $value = null, $ttl = 300)
	{
		$this->set($data, $value);
		$this->markAsTempdata(is_array($data) ? array_keys($data) : $data, $ttl);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns either a single piece of tempdata, or all temp data currently in the session.
	 *
	 * @param string $key   Session data key
	 *
	 * @return mixed        Session data value or null if not found.
	 */
	public function getTempdata($key = null)
	{
		if (isset($key))
		{
			return (isset($_SESSION['__ci_vars'], $_SESSION['__ci_vars'][$key], $_SESSION[$key]) &&
			        is_int($_SESSION['__ci_vars'][$key]))
				? $_SESSION[$key]
				: null;
		}

		$tempdata = [];

		if ( ! empty($_SESSION['__ci_vars']))
		{
			foreach ($_SESSION['__ci_vars'] as $key => &$value)
			{
				is_int($value) && $tempdata[$key] = $_SESSION[$key];
			}
		}

		return $tempdata;
	}

	//--------------------------------------------------------------------

	/**
	 * Removes a single piece of temporary data from the session.
	 *
	 * @param $key
	 */
	public function unsetTempdata($key)
	{
		$this->unmarkTempdata($key);
		unset($_SESSION[$key]);
	}

	//--------------------------------------------------------------------

	/**
	 * Mark one of more pieces of data as being temporary, meaning that
	 * it has a set lifespan within the session.
	 *
	 * @param     $key
	 * @param int $ttl
	 *
	 * @return bool
	 */
	public function markAsTempdata($key, $ttl = 300)
	{
		$ttl += time();

		if (is_array($key))
		{
			$temp = [];

			foreach ($key as $k => $v)
			{
				// Do we have a key => ttl pair, or just a key?
				if (is_int($k))
				{
					$k = $v;
					$v = $ttl;
				}
				else
				{
					$v += time();
				}

				if ( ! isset($_SESSION[$k]))
				{
					return false;
				}

				$temp[$k] = $v;
			}

			$_SESSION['__ci_vars'] = isset($_SESSION['__ci_vars'])
				? array_merge($_SESSION['__ci_vars'], $temp)
				: $temp;

			return true;
		}

		if ( ! isset($_SESSION[$key]))
		{
			return false;
		}

		$_SESSION['__ci_vars'][$key] = $ttl;

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Unmarks temporary data in the session, effectively removing its
	 * lifespan and allowing it to live as long as the session does.
	 *
	 * @param $key
	 */
	public function unmarkTempdata($key)
	{
		if (empty($_SESSION['__ci_vars']))
		{
			return;
		}

		is_array($key) OR $key = [$key];

		foreach ($key as $k)
		{
			if (isset($_SESSION['__ci_vars'][$k]) && is_int($_SESSION['__ci_vars'][$k]))
			{
				unset($_SESSION['__ci_vars'][$k]);
			}
		}

		if (empty($_SESSION['__ci_vars']))
		{
			unset($_SESSION['__ci_vars']);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Grabs the keys of all session data that has been marked as temporary data.
	 *
	 * @return array
	 */
	public function getTempKeys()
	{
		if ( ! isset($_SESSION['__ci_vars']))
		{
			return [];
		}

		$keys = [];
		foreach (array_keys($_SESSION['__ci_vars']) as $key)
		{
			is_int($_SESSION['__ci_vars'][$key]) && $keys[] = $key;
		}

		return $keys;
	}

	//--------------------------------------------------------------------

}
