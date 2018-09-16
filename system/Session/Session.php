<?php namespace CodeIgniter\Session;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
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
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
use Psr\Log\LoggerAwareTrait;

/**
 * Implementation of CodeIgniter session container.
 *
 * Session configuration is done through session variables and cookie related
 * variables in application/config/App.php
 */
class Session implements SessionInterface
{

	use LoggerAwareTrait;

	/**
	 * Instance of the driver to use.
	 *
	 * @var HandlerInterface
	 */
	protected $driver;

	/**
	 * The storage driver to use: files, database, redis, memcached
	 * @var string
	 */
	protected $sessionDriverName;

	/**
	 * The session cookie name, must contain only [0-9a-z_-] characters.
	 * @var string
	 */
	protected $sessionCookieName = 'ci_session';

	/**
	 * The number of SECONDS you want the session to last.
	 * Setting it to 0 (zero) means expire when the browser is closed.
	 * @var int
	 */
	protected $sessionExpiration = 7200;

	/**
	 * The location to save sessions to, driver dependent..
	 *
	 * For the 'files' driver, it's a path to a writable directory.
	 * WARNING: Only absolute paths are supported!
	 *
	 * For the 'database' driver, it's a table name.
	 *
	 * TODO: address memcache & redis needs
	 *
	 * IMPORTANT: You are REQUIRED to set a valid save path!
	 *
	 * @var string
	 */
	protected $sessionSavePath = null;

	/**
	 * Whether to match the user's IP address when reading the session data.
	 *
	 * WARNING: If you're using the database driver, don't forget to update
	 * your session table's PRIMARY KEY when changing this setting.
	 *
	 * @var bool
	 */
	protected $sessionMatchIP = false;

	/**
	 * How many seconds between CI regenerating the session ID.
	 * @var int
	 */
	protected $sessionTimeToUpdate = 300;

	/**
	 * Whether to destroy session data associated with the old session ID
	 * when auto-regenerating the session ID. When set to FALSE, the data
	 * will be later deleted by the garbage collector.
	 * @var bool
	 */
	protected $sessionRegenerateDestroy = false;

	/**
	 * The domain name to use for cookies.
	 * Set to .your-domain.com for site-wide cookies.
	 * @var string
	 */
	protected $cookieDomain = '';

	/**
	 * Path used for storing cookies.
	 * Typically will be a forward slash.
	 * @var string
	 */
	protected $cookiePath = '/';

	/**
	 * Cookie will only be set if a secure HTTPS connection exists.
	 * @var bool
	 */
	protected $cookieSecure = false;
	protected $sidRegexp;

	/**
	 * Logger instance to record error messages and awarnings.
	 * @var \PSR\Log\LoggerInterface
	 */
	protected $logger;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * Extract configuration settings and save them here.
	 *
	 * @param \SessionHandlerInterface $driver
	 * @param \Config\App $config
	 */
	public function __construct(\SessionHandlerInterface $driver, $config)
	{
		$this->driver = $driver;

		$this->sessionDriverName = $config->sessionDriver;
		$this->sessionCookieName = $config->sessionCookieName;
		$this->sessionExpiration = $config->sessionExpiration;
		$this->sessionSavePath = $config->sessionSavePath;
		$this->sessionMatchIP = $config->sessionMatchIP;
		$this->sessionTimeToUpdate = $config->sessionTimeToUpdate;
		$this->sessionRegenerateDestroy = $config->sessionRegenerateDestroy;

		$this->cookieDomain = $config->cookieDomain;
		$this->cookiePath = $config->cookiePath;
		$this->cookieSecure = $config->cookieSecure;

		helper('array');
	}

	//--------------------------------------------------------------------

	/**
	 * Initialize the session container and starts up the session.
	 */
	public function start()
	{
		if (is_cli() && ENVIRONMENT !== 'testing')
		{
			$this->logger->debug('Session: Initialization under CLI aborted.');

			return;
		}
		elseif ((bool) ini_get('session.auto_start'))
		{
			$this->logger->error('Session: session.auto_start is enabled in php.ini. Aborting.');

			return;
		}
		elseif (session_status() === PHP_SESSION_ACTIVE)
		{
			$this->logger->warning('Session: Sessions is enabled, and one exists.Please don\'t $session->start();');

			return;
		}

		if ( ! $this->driver instanceof \SessionHandlerInterface)
		{
			$this->logger->error("Session: Handler '" . $this->driver .
					"' doesn't implement SessionHandlerInterface. Aborting.");
		}

		$this->configure();

		$this->setSaveHandler();

		// Sanitize the cookie, because apparently PHP doesn't do that for userspace handlers
		if (isset($_COOKIE[$this->sessionCookieName]) && (
				! is_string($_COOKIE[$this->sessionCookieName]) || ! preg_match('#\A' . $this->sidRegexp . '\z#', $_COOKIE[$this->sessionCookieName])
				)
		)
		{
			unset($_COOKIE[$this->sessionCookieName]);
		}

		$this->startSession();

		// Is session ID auto-regeneration configured? (ignoring ajax requests)
		if ((empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
				strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') && ($regenerate_time = $this->sessionTimeToUpdate) > 0
		)
		{
			if ( ! isset($_SESSION['__ci_last_regenerate']))
			{
				$_SESSION['__ci_last_regenerate'] = time();
			}
			elseif ($_SESSION['__ci_last_regenerate'] < (time() - $regenerate_time))
			{
				$this->regenerate((bool) $this->sessionRegenerateDestroy);
			}
		}
		// Another work-around ... PHP doesn't seem to send the session cookie
		// unless it is being currently created or regenerated
		elseif (isset($_COOKIE[$this->sessionCookieName]) && $_COOKIE[$this->sessionCookieName] === session_id())
		{
			$this->setCookie();
		}

		$this->initVars();

		$this->logger->info("Session: Class initialized using '" . $this->sessionDriverName . "' driver.");

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Does a full stop of the session:
	 *
	 * - destroys the session
	 * - unsets the session id
	 * - destroys the session cookie
	 */
	public function stop()
	{
		setcookie(
				$this->sessionCookieName, session_id(), 1, $this->cookiePath, $this->cookieDomain, $this->cookieSecure, true
		);

		session_regenerate_id(true);
	}

	//--------------------------------------------------------------------

	/**
	 * Configuration.
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
				$this->sessionExpiration, $this->cookiePath, $this->cookieDomain, $this->cookieSecure, true // HTTP only; Yes, this is intentional and not configurable for security reasons.
		);

		if (empty($this->sessionExpiration))
		{
			$this->sessionExpiration = (int) ini_get('session.gc_maxlifetime');
		}
		else
		{
			ini_set('session.gc_maxlifetime', (int) $this->sessionExpiration);
		}

		// Security is king
		ini_set('session.use_trans_sid', 0);
		ini_set('session.use_strict_mode', 1);
		ini_set('session.use_cookies', 1);
		ini_set('session.use_only_cookies', 1);

		$this->configureSidLength();
	}

	// ------------------------------------------------------------------------

	/**
	 * Configure session ID length
	 *
	 * To make life easier, we used to force SHA-1 and 4 bits per
	 * character on everyone. And of course, someone was unhappy.
	 *
	 * Then PHP 7.1 broke backwards-compatibility because ext/session
	 * is such a mess that nobody wants to touch it with a pole stick,
	 * and the one guy who does, nobody has the energy to argue with.
	 *
	 * So we were forced to make changes, and OF COURSE something was
	 * going to break and now we have this pile of shit. -- Narf
	 *
	 * @return	void
	 */
	protected function configureSidLength()
	{
		$bits_per_character = (int) (ini_get('session.sid_bits_per_character') !== false
			? ini_get('session.sid_bits_per_character')
			: 4);
		$sid_length = (int) (ini_get('session.sid_length') !== false
			? ini_get('session.sid_length')
			: 40);
		if (($sid_length * $bits_per_character) < 160)
		{
			$bits = ($sid_length * $bits_per_character);
			// Add as many more characters as necessary to reach at least 160 bits
			$sid_length += (int) ceil((160 % $bits) / $bits_per_character);
			ini_set('session.sid_length', $sid_length);
		}

		// Yes, 4,5,6 are the only known possible values as of 2016-10-27
		switch ($bits_per_character)
		{
			case 4:
				$this->sidRegexp = '[0-9a-f]';
				break;
			case 5:
				$this->sidRegexp = '[0-9a-v]';
				break;
			case 6:
				$this->sidRegexp = '[0-9a-zA-Z,-]';
				break;
		}

		$this->sidRegexp .= '{' . $sid_length . '}';
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
		if (empty($_SESSION['__ci_vars']))
		{
			return;
		}

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

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Session Utility Methods
	//--------------------------------------------------------------------

	/**
	 * Regenerates the session ID.
	 *
	 * @param bool $destroy Should old session data be destroyed?
	 */
	public function regenerate(bool $destroy = false)
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
	 * If $data is a string, then it is interpreted as a session property
	 * key, and  $value is expected to be non-null.
	 *
	 * If $data is an array, it is expected to be an array of key/value pairs
	 * to be set as session properties.
	 *
	 * @param string|array $data  Property name or associative array of properties
	 * @param string|array $value Property value if single key provided
	 */
	public function set($data, $value = null)
	{
		if (is_array($data))
		{
			foreach ($data as $key => &$value)
			{
				if (is_int($key))
				{
					$_SESSION[$value] = null;
				}
				else
				{
					$_SESSION[$key] = $value;
				}
			}

			return;
		}

		$_SESSION[$data] = $value;
	}

	//--------------------------------------------------------------------

	/**
	 * Get user data that has been set in the session.
	 *
	 * If the property exists as "normal", returns it.
	 * Otherwise, returns an array of any temp or flash data values with the
	 * property key.
	 *
	 * Replaces the legacy method $session->userdata();
	 *
	 * @param  string $key	Identifier of the session property to retrieve
	 * @return array|null	The property value(s)
	 */
	public function get(string $key = null)
	{
		if (! empty($key) && $value = dot_array_search($key, $_SESSION))
		{
			return $value;
		}
		elseif (empty($_SESSION))
		{
			return [];
		}

		if (! empty($key))
		{
			return null;
		}

		$userdata = [];
		$_exclude = array_merge(
			['__ci_vars'], $this->getFlashKeys(), $this->getTempKeys()
		);

		$keys = array_keys($_SESSION);
		foreach ($keys as $key)
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
	 * @param string	$key	Identifier of the session property we are interested in.
	 *
	 * @return bool
	 */
	public function has(string $key)
	{
		return isset($_SESSION[$key]);
	}

       //--------------------------------------------------------------------

      /**
	 * Push new value onto session value that is array.
	 *
	 * @param string	   $key	Identifier of the session property we are interested in.
       * @param array            $data   value to be pushed to existing session key.
	 *
	 * @return void
	 */
	public function push(string $key, array $data)
	{
               if ($this->has($key) && is_array($value = $this->get($key)))
               {
                   $this->set($key, array_merge($value, $data));
               }
	}

	//--------------------------------------------------------------------

	/**
	 * Remove one or more session properties.
	 *
	 * If $key is an array, it is interpreted as an array of string property
	 * identifiers to remove. Otherwise, it is interpreted as the identifier
	 * of a specific session property to remove.
	 *
	 * @param  string|array $key Identifier of the session property or properties to remove.
	 */
	public function remove($key)
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
	 * @param  string $key Identifier of the session property to set.
	 * @param         $value
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
	 * @param  string $key Identifier of the session property to remove.
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
		elseif ($key === 'session_id')
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
	 * If $data is an array, it is interpreted as an associative array of
	 * key/value pairs for flashdata properties.
	 * Otherwise, it is interpreted as the identifier of a specific
	 * flashdata property, with $value containing the property value.
	 *
	 * @param array|string $data  Property identifier or associative array of properties
	 * @param string|array $value Property value if $data is a scalar
	 */
	public function setFlashdata($data, $value = null)
	{
		$this->set($data, $value);
		$this->markAsFlashdata(is_array($data) ? array_keys($data) : $data);
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve one or more items of flash data from the session.
	 *
	 * If the item key is null, return all flashdata.
	 *
	 * @param string $key	Property identifier
	 * @return array|null	The requested property value, or an associative array  of them
	 */
	public function getFlashdata(string $key = null)
	{
		if (isset($key))
		{
			return (isset($_SESSION['__ci_vars'], $_SESSION['__ci_vars'][$key], $_SESSION[$key]) &&
					! is_int($_SESSION['__ci_vars'][$key])) ? $_SESSION[$key] : null;
		}

		$flashdata = [];

		if ( ! empty($_SESSION['__ci_vars']))
		{
			foreach ($_SESSION['__ci_vars'] as $key => &$value)
			{
				is_int($value) || $flashdata[$key] = $_SESSION[$key];
			}
		}

		return $flashdata;
	}

	//--------------------------------------------------------------------

	/**
	 * Keeps a single piece of flash data alive for one more request.
	 *
	 * @param string $key	Property identifier or array of them
	 */
	public function keepFlashdata(string $key)
	{
		$this->markAsFlashdata($key);
	}

	//--------------------------------------------------------------------

	/**
	 * Mark a session property or properties as flashdata.
	 *
	 * @param array|string $key Property identifier or array of them
	 *
	 * @return bool False if any of the properties are not already set
	 */
	public function markAsFlashdata($key)
	{
		if (is_array($key))
		{
			for ($i = 0, $c = count($key); $i < $c; $i ++ )
			{
				if ( ! isset($_SESSION[$key[$i]]))
				{
					return false;
				}
			}

			$new = array_fill_keys($key, 'new');

			$_SESSION['__ci_vars'] = isset($_SESSION['__ci_vars']) ? array_merge($_SESSION['__ci_vars'], $new) : $new;

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
	 * @param mixed $key	Property identifier or array of them
	 */
	public function unmarkFlashdata($key)
	{
		if (empty($_SESSION['__ci_vars']))
		{
			return;
		}

		is_array($key) || $key = [$key];

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
	 * Retrieve all of the keys for session data marked as flashdata.
	 *
	 * @return array	The property names of all flashdata
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
			is_int($_SESSION['__ci_vars'][$key]) || $keys[] = $key;
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
	 * @param string|array $data  Session data key or associative array of items
	 * @param null         $value Value to store
	 * @param int          $ttl   Time-to-live in seconds
	 */
	public function setTempdata($data, $value = null, $ttl = 300)
	{
		$this->set($data, $value);
		$this->markAsTempdata($data, $ttl);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns either a single piece of tempdata, or all temp data currently
	 * in the session.
	 *
	 * @param  $key   Session data key
	 * @return mixed        Session data value or null if not found.
	 */
	public function getTempdata($key = null)
	{
		if (isset($key))
		{
			return (isset($_SESSION['__ci_vars'], $_SESSION['__ci_vars'][$key], $_SESSION[$key]) &&
					is_int($_SESSION['__ci_vars'][$key])) ? $_SESSION[$key] : null;
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
	 * @param string $key Session data key
	 */
	public function removeTempdata($key)
	{
		$this->unmarkTempdata($key);
		unset($_SESSION[$key]);
	}

	//--------------------------------------------------------------------

	/**
	 * Mark one of more pieces of data as being temporary, meaning that
	 * it has a set lifespan within the session.
	 *
	 * @param string|array $key Property identifier or array of them
	 * @param int          $ttl Time to live, in seconds
	 *
	 * @return bool    False if any of the properties were not set
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
				elseif (is_string($v))
				{
					$v = time() + $ttl;
				}
				else
				{
					$v += time();
				}

				if ( ! array_key_exists($k, $_SESSION))
				{
					return false;
				}

				$temp[$k] = $v;
			}

			$_SESSION['__ci_vars'] = isset($_SESSION['__ci_vars']) ? array_merge($_SESSION['__ci_vars'], $temp) : $temp;

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
	 * @param string|array $key	Property identifier or array of them
	 */
	public function unmarkTempdata($key)
	{
		if (empty($_SESSION['__ci_vars']))
		{
			return;
		}

		is_array($key) || $key = [$key];

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
	 * Retrieve the keys of all session data that have been marked as temporary data.
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

	/**
	 * Sets the driver as the session handler in PHP.
	 * Extracted for easier testing.
	 */
	protected function setSaveHandler()
	{
		session_set_save_handler($this->driver, true);
	}

	//--------------------------------------------------------------------

	/**
	 * Starts the session.
	 * Extracted for testing reasons.
	 */
	protected function startSession()
	{
		if (ENVIRONMENT === 'testing')
		{
			$_SESSION = [];
			return;
		}

		session_start();
	}

	//--------------------------------------------------------------------

	/**
	 * Takes care of setting the cookie on the client side.
	 * Extracted for testing reasons.
	 */
	protected function setCookie()
	{
		setcookie(
				$this->sessionCookieName, session_id(), (empty($this->sessionExpiration) ? 0 : time() + $this->sessionExpiration), $this->cookiePath, $this->cookieDomain, $this->cookieSecure, true
		);
	}

	//--------------------------------------------------------------------
}
