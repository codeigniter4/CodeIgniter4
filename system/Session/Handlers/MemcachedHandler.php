<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Session\Handlers;

use CodeIgniter\Session\Exceptions\SessionException;
use Config\App as AppConfig;
use Memcached;

/**
 * Session handler using Memcache for persistence
 */
class MemcachedHandler extends BaseHandler
{
	/**
	 * Memcached instance
	 *
	 * @var Memcached|null
	 */
	protected $memcached;

	/**
	 * Key prefix
	 *
	 * @var string
	 */
	protected $keyPrefix = 'ci_session:';

	/**
	 * Lock key
	 *
	 * @var string|null
	 */
	protected $lockKey;

	/**
	 * Number of seconds until the session ends.
	 *
	 * @var integer
	 */
	protected $sessionExpiration = 7200;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param  AppConfig $config
	 * @param  string    $ipAddress
	 * @throws SessionException
	 */
	public function __construct(AppConfig $config, string $ipAddress)
	{
		parent::__construct($config, $ipAddress);

		if (empty($this->savePath))
		{
			throw SessionException::forEmptySavepath();
		}

		if ($this->matchIP === true)
		{
			$this->keyPrefix .= $this->ipAddress . ':';
		}

		if (! empty($this->keyPrefix))
		{
			ini_set('memcached.sess_prefix', $this->keyPrefix);
		}

		$this->sessionExpiration = $config->sessionExpiration;
	}

	//--------------------------------------------------------------------

	/**
	 * Open
	 *
	 * Sanitizes save_path and initializes connections.
	 *
	 * @param string $savePath Server path(s)
	 * @param string $name     Session cookie name, unused
	 *
	 * @return boolean
	 */
	public function open($savePath, $name): bool
	{
		$this->memcached = new Memcached();
		$this->memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true); // required for touch() usage

		$serverList = [];

		foreach ($this->memcached->getServerList() as $server)
		{
			$serverList[] = $server['host'] . ':' . $server['port'];
		}

		if (! preg_match_all('#,?([^,:]+)\:(\d{1,5})(?:\:(\d+))?#', $this->savePath, $matches, PREG_SET_ORDER)
		)
		{
			$this->memcached = null;
			$this->logger->error('Session: Invalid Memcached save path format: ' . $this->savePath);

			return false;
		}

		foreach ($matches as $match)
		{
			// If Memcached already has this server (or if the port is invalid), skip it
			if (in_array($match[1] . ':' . $match[2], $serverList, true))
			{
				$this->logger->debug('Session: Memcached server pool already has ' . $match[1] . ':' . $match[2]);
				continue;
			}

			if (! $this->memcached->addServer($match[1], $match[2], $match[3] ?? 0))
			{
				$this->logger->error('Could not add ' . $match[1] . ':' . $match[2] . ' to Memcached server pool.');
			}
			else
			{
				$serverList[] = $match[1] . ':' . $match[2];
			}
		}

		if (empty($serverList))
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
	 * @param string $sessionID Session ID
	 *
	 * @return string    Serialized session data
	 */
	public function read($sessionID): string
	{
		if (isset($this->memcached) && $this->lockSession($sessionID))
		{
			// Needed by write() to detect session_regenerate_id() calls
			if (is_null($this->sessionID)) // @phpstan-ignore-line
			{
				$this->sessionID = $sessionID;
			}

			$sessionData       = (string) $this->memcached->get($this->keyPrefix . $sessionID);
			$this->fingerprint = md5($sessionData);

			return $sessionData;
		}

		return '';
	}

	//--------------------------------------------------------------------

	/**
	 * Write
	 *
	 * Writes (create / update) session data
	 *
	 * @param string $sessionID   Session ID
	 * @param string $sessionData Serialized session data
	 *
	 * @return boolean
	 */
	public function write($sessionID, $sessionData): bool
	{
		if (! isset($this->memcached))
		{
			return false;
		}

		// Was the ID regenerated?
		if ($sessionID !== $this->sessionID)
		{
			if (! $this->releaseLock() || ! $this->lockSession($sessionID))
			{
				return false;
			}

			$this->fingerprint = md5('');
			$this->sessionID   = $sessionID;
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
	 * @return boolean
	 */
	public function close(): bool
	{
		if (isset($this->memcached))
		{
			isset($this->lockKey) && $this->memcached->delete($this->lockKey);

			if (! $this->memcached->quit())
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
	 * @param string $sessionId Session ID
	 *
	 * @return boolean
	 */
	public function destroy($sessionId): bool
	{
		if (isset($this->memcached, $this->lockKey))
		{
			$this->memcached->delete($this->keyPrefix . $sessionId);

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
	 * @param integer $maxlifetime Maximum lifetime of sessions
	 *
	 * @return boolean
	 */
	public function gc($maxlifetime): bool
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
	 * @param string $sessionID Session ID
	 *
	 * @return boolean
	 */
	protected function lockSession(string $sessionID): bool
	{
		if (isset($this->lockKey))
		{
			return $this->memcached->replace($this->lockKey, time(), 300);
		}

		// 30 attempts to obtain a lock, in case another request already has it
		$lockKey = $this->keyPrefix . $sessionID . ':lock';
		$attempt = 0;

		do
		{
			if ($this->memcached->get($lockKey))
			{
				sleep(1);
				continue;
			}

			if (! $this->memcached->set($lockKey, time(), 300))
			{
				$this->logger->error('Session: Error while trying to obtain lock for ' . $this->keyPrefix . $sessionID);

				return false;
			}

			$this->lockKey = $lockKey;
			break;
		}
		while (++ $attempt < 30);

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
	 * @return boolean
	 */
	protected function releaseLock(): bool
	{
		if (isset($this->memcached, $this->lockKey) && $this->lock)
		{
			if (! $this->memcached->delete($this->lockKey) &&
					$this->memcached->getResultCode() !== Memcached::RES_NOTFOUND
			)
			{
				$this->logger->error('Session: Error while trying to free lock for ' . $this->lockKey);

				return false;
			}

			$this->lockKey = null;
			$this->lock    = false;
		}

		return true;
	}

	//--------------------------------------------------------------------
}
