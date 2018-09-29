<?php namespace CodeIgniter\Session\Handlers;

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
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Session\Exceptions\SessionException;

/**
 * Session handler using Memcache for persistence
 */
class MemcachedHandler extends BaseHandler implements \SessionHandlerInterface
{

	/**
	 * Memcached instance
	 *
	 * @var    \Memcached
	 */
	protected $memcached;

	/**
	 * Key prefix
	 *
	 * @var    string
	 */
	protected $keyPrefix = 'ci_session:';

	/**
	 * Lock key
	 *
	 * @var    string
	 */
	protected $lockKey;

	/**
	 * Number of seconds until the session ends.
	 *
	 * @var int
	 */
	protected $sessionExpiration = 7200;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param BaseConfig $config
	 * @throws \CodeIgniter\Session\Exceptions\SessionException
	 */
	public function __construct(BaseConfig $config)
	{
		parent::__construct($config);

		if (empty($this->savePath))
		{
			throw SessionException::forEmptySavepath();
		}

		if ($this->matchIP === true)
		{
			$this->keyPrefix .= $_SERVER['REMOTE_ADDR'] . ':';
		}

		$this->sessionExpiration = $config->sessionExpiration;
	}

	//--------------------------------------------------------------------

	/**
	 * Open
	 *
	 * Sanitizes save_path and initializes connections.
	 *
	 * @param    string $save_path Server path(s)
	 * @param    string $name      Session cookie name, unused
	 *
	 * @return    bool
	 */
	public function open($save_path, $name)
	{
		$this->memcached = new \Memcached();
		$this->memcached->setOption(\Memcached::OPT_BINARY_PROTOCOL, true); // required for touch() usage

		$server_list = [];

		foreach ($this->memcached->getServerList() as $server)
		{
			$server_list[] = $server['host'] . ':' . $server['port'];
		}

		if ( ! preg_match_all('#,?([^,:]+)\:(\d{1,5})(?:\:(\d+))?#', $this->savePath, $matches, PREG_SET_ORDER)
		)
		{
			$this->memcached = null;
			$this->logger->error('Session: Invalid Memcached save path format: ' . $this->savePath);

			return false;
		}

		foreach ($matches as $match)
		{
			// If Memcached already has this server (or if the port is invalid), skip it
			if (in_array($match[1] . ':' . $match[2], $server_list, true))
			{
				$this->logger->debug('Session: Memcached server pool already has ' . $match[1] . ':' . $match[2]);
				continue;
			}

			if ( ! $this->memcached->addServer($match[1], $match[2], $match[3] ?? 0))
			{
				$this->logger->error('Could not add ' . $match[1] . ':' . $match[2] . ' to Memcached server pool.');
			}
			else
			{
				$server_list[] = $match[1] . ':' . $match[2];
			}
		}

		if (empty($server_list))
		{
			$this->logger->error('Session: Memcached server pool is empty.');

			return false;
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Read
	 *
	 * Reads session data and acquires a lock
	 *
	 * @param    string $sessionID Session ID
	 *
	 * @return    string    Serialized session data
	 */
	public function read($sessionID)
	{
		if (isset($this->memcached) && $this->lockSession($sessionID))
		{
			// Needed by write() to detect session_regenerate_id() calls
			$this->sessionID = $sessionID;

			$session_data = (string) $this->memcached->get($this->keyPrefix . $sessionID);
			$this->fingerprint = md5($session_data);

			return $session_data;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Write
	 *
	 * Writes (create / update) session data
	 *
	 * @param    string $sessionID   Session ID
	 * @param    string $sessionData Serialized session data
	 *
	 * @return    bool
	 */
	public function write($sessionID, $sessionData)
	{
		if ( ! isset($this->memcached))
		{
			return false;
		}
		// Was the ID regenerated?
		elseif ($sessionID !== $this->sessionID)
		{
			if ( ! $this->releaseLock() || ! $this->lockSession($sessionID))
			{
				return false;
			}

			$this->fingerprint = md5('');
			$this->sessionID = $sessionID;
		}

		if (isset($this->lockKey))
		{
			$this->memcached->replace($this->lockKey, time(), 300);

			if ($this->fingerprint !== ($fingerprint = md5($sessionData)))
			{
				if ($this->memcached->set($this->keyPrefix . $sessionID, $sessionData, $this->sessionExpiration))
				{
					$this->fingerprint = $fingerprint;

					return true;
				}

				return false;
			}

			return $this->memcached->touch($this->keyPrefix . $sessionID, $this->sessionExpiration);
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Close
	 *
	 * Releases locks and closes connection.
	 *
	 * @return    bool
	 */
	public function close()
	{
		if (isset($this->memcached))
		{
			isset($this->lockKey) && $this->memcached->delete($this->lockKey);

			if ( ! $this->memcached->quit())
			{
				return false;
			}

			$this->memcached = null;

			return true;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Destroy
	 *
	 * Destroys the current session.
	 *
	 * @param    string $session_id Session ID
	 *
	 * @return    bool
	 */
	public function destroy($session_id)
	{
		if (isset($this->memcached, $this->lockKey))
		{
			$this->memcached->delete($this->keyPrefix . $session_id);

			return $this->destroyCookie();
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Garbage Collector
	 *
	 * Deletes expired sessions
	 *
	 * @param    int $maxlifetime Maximum lifetime of sessions
	 *
	 * @return    bool
	 */
	public function gc($maxlifetime)
	{
		// Not necessary, Memcached takes care of that.
		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Get lock
	 *
	 * Acquires an (emulated) lock.
	 *
	 * @param    string $sessionID Session ID
	 *
	 * @return    bool
	 */
	protected function lockSession(string $sessionID): bool
	{
		if (isset($this->lockKey))
		{
			return $this->memcached->replace($this->lockKey, time(), 300);
		}

		// 30 attempts to obtain a lock, in case another request already has it
		$lock_key = $this->keyPrefix . $sessionID . ':lock';
		$attempt = 0;

		do
		{
			if ($this->memcached->get($lock_key))
			{
				sleep(1);
				continue;
			}

			if ( ! $this->memcached->set($lock_key, time(), 300))
			{
				$this->logger->error('Session: Error while trying to obtain lock for ' . $this->keyPrefix . $sessionID);

				return false;
			}

			$this->lockKey = $lock_key;
			break;
		} while (++ $attempt < 30);

		if ($attempt === 30)
		{
			$this->logger->error('Session: Unable to obtain lock for ' . $this->keyPrefix . $sessionID . ' after 30 attempts, aborting.');

			return false;
		}

		$this->lock = true;

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Release lock
	 *
	 * Releases a previously acquired lock
	 *
	 * @return    bool
	 */
	protected function releaseLock(): bool
	{
		if (isset($this->memcached, $this->lockKey) && $this->lock)
		{
			if ( ! $this->memcached->delete($this->lockKey) &&
					$this->memcached->getResultCode() !== \Memcached::RES_NOTFOUND
			)
			{
				$this->logger->error('Session: Error while trying to free lock for ' . $this->lockKey);

				return false;
			}

			$this->lockKey = null;
			$this->lock = false;
		}

		return true;
	}

	//--------------------------------------------------------------------
}
