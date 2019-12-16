<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019 CodeIgniter Foundation
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
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Session\Handlers;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Session\Exceptions\SessionException;

/**
 * Session handler using Redis for persistence
 */
class RedisHandler extends BaseHandler implements \SessionHandlerInterface
{

	/**
	 * phpRedis instance
	 *
	 * @var resource
	 */
	protected $redis;

	/**
	 * Key prefix
	 *
	 * @var string
	 */
	protected $keyPrefix = 'ci_session:';

	/**
	 * Lock key
	 *
	 * @var string
	 */
	protected $lockKey;

	/**
	 * Key exists flag
	 *
	 * @var boolean
	 */
	protected $keyExists = false;

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
	 * @param BaseConfig $config
	 * @param string     $ipAddress
	 *
	 * @throws \Exception
	 */
	public function __construct(BaseConfig $config, string $ipAddress)
	{
		parent::__construct($config, $ipAddress);

		if (empty($this->savePath))
		{
			throw SessionException::forEmptySavepath();
		}
		elseif (preg_match('#(?:tcp://)?([^:?]+)(?:\:(\d+))?(\?.+)?#', $this->savePath, $matches))
		{
			isset($matches[3]) || $matches[3] = ''; // Just to avoid undefined index notices below

			$this->savePath = [
				'host'     => $matches[1],
				'port'     => empty($matches[2]) ? null : $matches[2],
				'password' => preg_match('#auth=([^\s&]+)#', $matches[3], $match) ? $match[1] : null,
				'database' => preg_match('#database=(\d+)#', $matches[3], $match) ? (int) $match[1] : null,
				'timeout'  => preg_match('#timeout=(\d+\.\d+)#', $matches[3], $match) ? (float) $match[1] : null,
			];

			preg_match('#prefix=([^\s&]+)#', $matches[3], $match) && $this->keyPrefix = $match[1];
		}
		else
		{
			throw SessionException::forInvalidSavePathFormat($this->savePath);
		}

		if ($this->matchIP === true)
		{
			$this->keyPrefix .= $this->ipAddress . ':';
		}

				$this->sessionExpiration = $config->sessionExpiration;
	}

	//--------------------------------------------------------------------

	/**
	 * Open
	 *
	 * Sanitizes save_path and initializes connection.
	 *
	 * @param  string $save_path Server path
	 * @param  string $name      Session cookie name, unused
	 * @return boolean
	 */
	public function open($save_path, $name): bool
	{
		if (empty($this->savePath))
		{
			return false;
		}

		$redis = new \Redis();

		if (! $redis->connect($this->savePath['host'], $this->savePath['port'], $this->savePath['timeout']))
		{
			$this->logger->error('Session: Unable to connect to Redis with the configured settings.');
		}
		elseif (isset($this->savePath['password']) && ! $redis->auth($this->savePath['password']))
		{
			$this->logger->error('Session: Unable to authenticate to Redis instance.');
		}
		elseif (isset($this->savePath['database']) && ! $redis->select($this->savePath['database']))
		{
			$this->logger->error('Session: Unable to select Redis database with index ' . $this->savePath['database']);
		}
		else
		{
			$this->redis = $redis;
			return true;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Read
	 *
	 * Reads session data and acquires a lock
	 *
	 * @param string $sessionID Session ID
	 *
	 * @return string|false	Serialized session data
	 */
	public function read($sessionID): string
	{
		if (isset($this->redis) && $this->lockSession($sessionID))
		{
			// Needed by write() to detect session_regenerate_id() calls
			if (is_null($this->sessionID))
			{
				$this->sessionID = $sessionID;
			}

			$session_data                               = $this->redis->get($this->keyPrefix . $sessionID);
			is_string($session_data) ? $this->keyExists = true : $session_data = '';

			$this->fingerprint = md5($session_data);

			return $session_data;
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
		if (! isset($this->redis))
		{
			return false;
		}
		// Was the ID regenerated?
		elseif ($sessionID !== $this->sessionID)
		{
			if (! $this->releaseLock() || ! $this->lockSession($sessionID))
			{
				return false;
			}

			$this->keyExists = false;
			$this->sessionID = $sessionID;
		}

		if (isset($this->lockKey))
		{
			$this->redis->expire($this->lockKey, 300);

			if ($this->fingerprint !== ($fingerprint = md5($sessionData)) || $this->keyExists === false)
			{
				if ($this->redis->set($this->keyPrefix . $sessionID, $sessionData, $this->sessionExpiration))
				{
					$this->fingerprint = $fingerprint;
					$this->keyExists   = true;
					return true;
				}

				return false;
			}

			return $this->redis->expire($this->keyPrefix . $sessionID, $this->sessionExpiration);
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
		if (isset($this->redis))
		{
			try
			{
				$ping_reply = $this->redis->ping();
				if (($ping_reply === true) || ($ping_reply === '+PONG'))
				{
					isset($this->lockKey) && $this->redis->del($this->lockKey);

					if (! $this->redis->close())
					{
						return false;
					}
				}
			}
			catch (\RedisException $e)
			{
				$this->logger->error('Session: Got RedisException on close(): ' . $e->getMessage());
			}

			$this->redis = null;

			return true;
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Destroy
	 *
	 * Destroys the current session.
	 *
	 * @param string $sessionID
	 *
	 * @return boolean
	 */
	public function destroy($sessionID): bool
	{
		if (isset($this->redis, $this->lockKey))
		{
			if (($result = $this->redis->del($this->keyPrefix . $sessionID)) !== 1)
			{
				$this->logger->debug('Session: Redis::del() expected to return 1, got ' . var_export($result, true) . ' instead.');
			}

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
	 * @param  integer $maxlifetime Maximum lifetime of sessions
	 * @return boolean
	 */
	public function gc($maxlifetime): bool
	{
		// Not necessary, Redis takes care of that.
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
		// PHP 7 reuses the SessionHandler object on regeneration,
		// so we need to check here if the lock key is for the
		// correct session ID.
		if ($this->lockKey === $this->keyPrefix . $sessionID . ':lock')
		{
			return $this->redis->expire($this->lockKey, 300);
		}

		// 30 attempts to obtain a lock, in case another request already has it
		$lock_key = $this->keyPrefix . $sessionID . ':lock';
		$attempt  = 0;

		do
		{
			if (($ttl = $this->redis->ttl($lock_key)) > 0)
			{
				sleep(1);
				continue;
			}

			if (! $this->redis->setex($lock_key, 300, time()))
			{
				$this->logger->error('Session: Error while trying to obtain lock for ' . $this->keyPrefix . $sessionID);
				return false;
			}

			$this->lockKey = $lock_key;
			break;
		}
		while (++ $attempt < 30);

		if ($attempt === 30)
		{
			log_message('error', 'Session: Unable to obtain lock for ' . $this->keyPrefix . $sessionID . ' after 30 attempts, aborting.');
			return false;
		}
		elseif ($ttl === -1)
		{
			log_message('debug', 'Session: Lock for ' . $this->keyPrefix . $sessionID . ' had no TTL, overriding.');
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
		if (isset($this->redis, $this->lockKey) && $this->lock)
		{
			if (! $this->redis->del($this->lockKey))
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
