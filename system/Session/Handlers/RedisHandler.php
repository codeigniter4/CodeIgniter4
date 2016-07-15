<?php namespace CodeIgniter\Session\Handlers;

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

use CodeIgniter\Config\BaseConfig;

/**
 * Session handler using Redis for persistence
 */
class RedisHandler extends BaseHandler implements \SessionHandlerInterface
{

	/**
	 * phpRedis instance
	 *
	 * @var    resource
	 */
	protected $redis;

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
	 * @throws \Exception
	 */
	public function __construct(BaseConfig $config)
	{
		parent::__construct($config);

		if (empty($this->savePath))
		{
			throw new \Exception('Session: No Redis save path configured.');
		}
		elseif (preg_match('#(?:tcp://)?([^:?]+)(?:\:(\d+))?(\?.+)?#', $this->savePath, $matches))
		{
			isset($matches[3]) OR $matches[3] = ''; // Just to avoid undefined index notices below

			$this->savePath = [
				'host'     => $matches[1],
				'port'     => empty($matches[2]) ? null : $matches[2],
				'password' => preg_match('#auth=([^\s&]+)#', $matches[3], $match) ? $match[1] : null,
				'database' => preg_match('#database=(\d+)#', $matches[3], $match) ? (int)$match[1] : null,
				'timeout'  => preg_match('#timeout=(\d+\.\d+)#', $matches[3], $match) ? (float)$match[1] : null,
			];

			preg_match('#prefix=([^\s&]+)#', $matches[3], $match) && $this->keyPrefix = $match[1];
		}
		else
		{
			throw new \Exception('Session: Invalid Redis save path format: '.$this->savePath);
		}

		if ($this->matchIP === true)
		{
			$this->keyPrefix .= $_SERVER['REMOTE_ADDR'].':';
		}

		$this->sessionExpiration = $config->sessionExpiration;
	}

	//--------------------------------------------------------------------

	/**
	 * Open
	 *
	 * Sanitizes save_path and initializes connection.
	 *
	 * @param	string	$save_path	Server path
	 * @param	string	$name		Session cookie name, unused
	 * @return	bool
	 */
	public function open($save_path, $name)
	{
		if (empty($this->savePath))
		{
			return FALSE;
		}

		$redis = new \Redis();

		if ( ! $redis->connect($this->savePath['host'], $this->savePath['port'], $this->savePath['timeout']))
		{
			$this->logger->error('Session: Unable to connect to Redis with the configured settings.');
		}
		elseif (isset($this->_config['save_path']['password']) && ! $redis->auth($this->_config['save_path']['password']))
		{
			$this->logger->error('Session: Unable to authenticate to Redis instance.');
		}
		elseif (isset($this->_config['save_path']['database']) && ! $redis->select($this->_config['save_path']['database']))
		{
			$this->logger->error('Session: Unable to select Redis database with index '.$this->_config['save_path']['database']);
		}
		else
		{
			$this->redis = $redis;
			return TRUE;
		}

		return FALSE;
	}

	//--------------------------------------------------------------------

	/**
	 * Read
	 *
	 * Reads session data and acquires a lock
	 *
	 * @param	string	$session_id	Session ID
	 * @return	string	Serialized session data
	 */
	public function read($session_id)
	{
		if (isset($this->redis) && $this->lockSession($session_id))
		{
			// Needed by write() to detect session_regenerate_id() calls
			$this->_session_id = $session_id;

			$session_data = (string) $this->redis->get($this->keyPrefix.$session_id);
			$this->_fingerprint = md5($session_data);

			return $session_data;
		}

		return FALSE;
	}

	//--------------------------------------------------------------------

	/**
	 * Write
	 *
	 * Writes (create / update) session data
	 *
	 * @param	string	$session_id	Session ID
	 * @param	string	$session_data	Serialized session data
	 * @return	bool
	 */
	public function write($session_id, $session_data)
	{
		if ( ! isset($this->redis))
		{
			return FALSE;
		}
		// Was the ID regenerated?
		elseif ($session_id !== $this->sessionID)
		{
			if ( ! $this->releaseLock() || ! $this->lockSession($session_id))
			{
				return FALSE;
			}

			$this->_fingerprint = md5('');
			$this->_session_id = $session_id;
		}

		if (isset($this->lockKey))
		{
			$this->redis->setTimeout($this->lockKey, 300);

			if ($this->fingerprint !== ($fingerprint = md5($session_data)))
			{
				if ($this->redis->set($this->keyPrefix.$session_id, $session_data, $this->sessionExpiration))
				{
					$this->_fingerprint = $fingerprint;
					return TRUE;
				}

				return FALSE;
			}

			return $this->redis->setTimeout($this->keyPrefix.$session_id, $this->sessionExpiration);
		}

		return FALSE;
	}

	//--------------------------------------------------------------------

	/**
	 * Close
	 *
	 * Releases locks and closes connection.
	 *
	 * @return	bool
	 */
	public function close()
	{
		if (isset($this->redis))
		{
			try {
				if ($this->redis->ping() === '+PONG')
				{
					isset($this->lockKey) && $this->redis->delete($this->lockKey);

					if ( ! $this->redis->close())
					{
						return FALSE;
					}
				}
			}
			catch (\RedisException $e)
			{
				$this->logger->error('Session: Got RedisException on close(): '.$e->getMessage());
			}

			$this->redis = NULL;

			return TRUE;
		}

		return TRUE;
	}

	//--------------------------------------------------------------------

	/**
	 * Destroy
	 *
	 * Destroys the current session.
	 *
	 * @param	string	$session_id	Session ID
	 * @return	bool
	 */
	public function destroy($session_id)
	{
		if (isset($this->redis, $this->lockKey))
		{
			if (($result = $this->redis->delete($this->keyPrefix.$session_id)) !== 1)
			{
				$this->logger->debug('Session: Redis::delete() expected to return 1, got '.var_export($result, TRUE).' instead.');
			}

			return $this->destroyCookie();
		}

		return FALSE;
	}

	//--------------------------------------------------------------------

	/**
	 * Garbage Collector
	 *
	 * Deletes expired sessions
	 *
	 * @param	int 	$maxlifetime	Maximum lifetime of sessions
	 * @return	bool
	 */
	public function gc($maxlifetime)
	{
		// Not necessary, Redis takes care of that.
		return TRUE;
	}

	//--------------------------------------------------------------------

	/**
	 * Get lock
	 *
	 * Acquires an (emulated) lock.
	 *
	 * @param	string	$session_id	Session ID
	 * @return	bool
	 */
	protected function lockSession(string $session_id): bool
	{
		if (isset($this->lockKey))
		{
			return $this->redis->setTimeout($this->lockKey, 300);
		}

		// 30 attempts to obtain a lock, in case another request already has it
		$lock_key = $this->keyPrefix.$session_id.':lock';
		$attempt = 0;

		do
		{
			if (($ttl = $this->redis->ttl($lock_key)) > 0)
			{
				sleep(1);
				continue;
			}

			if ( ! $this->redis->setex($lock_key, 300, time()))
			{
				$this->logger->error('Session: Error while trying to obtain lock for '.$this->keyPrefix.$session_id);
				return FALSE;
			}

			$this->lockKey = $lock_key;
			break;
		}
		while (++$attempt < 30);

		if ($attempt === 30)
		{
			log_message('error', 'Session: Unable to obtain lock for '.$this->keyPrefix.$session_id.' after 30 attempts, aborting.');
			return FALSE;
		}
		elseif ($ttl === -1)
		{
			log_message('debug', 'Session: Lock for '.$this->keyPrefix.$session_id.' had no TTL, overriding.');
		}

		$this->lock = TRUE;
		return TRUE;
	}

	//--------------------------------------------------------------------

	/**
	 * Release lock
	 *
	 * Releases a previously acquired lock
	 *
	 * @return	bool
	 */
	protected function releaseLock(): bool
	{
		if (isset($this->redis, $this->lockKey) && $this->lock)
		{
			if ( ! $this->redis->delete($this->lockKey))
			{
				$this->logger->error('Session: Error while trying to free lock for '.$this->lockKey);
				return FALSE;
			}

			$this->lockKey = NULL;
			$this->lock    = FALSE;
		}

		return TRUE;
	}
	
	//--------------------------------------------------------------------
	
}
