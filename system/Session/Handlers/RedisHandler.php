<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Session\Handlers;

use CodeIgniter\I18n\Time;
use CodeIgniter\Session\Exceptions\SessionException;
use Config\App as AppConfig;
use Redis;
use RedisException;
use ReturnTypeWillChange;

/**
 * Session handler using Redis for persistence
 */
class RedisHandler extends BaseHandler
{
    private const DEFAULT_PORT = 6379;

    /**
     * phpRedis instance
     *
     * @var Redis|null
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
     * @var string|null
     */
    protected $lockKey;

    /**
     * Key exists flag
     *
     * @var bool
     */
    protected $keyExists = false;

    /**
     * Number of seconds until the session ends.
     *
     * @var int
     */
    protected $sessionExpiration = 7200;

    /**
     * @param string $ipAddress User's IP address
     *
     * @throws SessionException
     */
    public function __construct(AppConfig $config, string $ipAddress)
    {
        parent::__construct($config, $ipAddress);

        $this->setSavePath();

        // Add sessionCookieName for multiple session cookies.
        $this->keyPrefix .= $config->sessionCookieName . ':';

        if ($this->matchIP === true) {
            $this->keyPrefix .= $this->ipAddress . ':';
        }

        $this->sessionExpiration = empty($config->sessionExpiration)
            ? (int) ini_get('session.gc_maxlifetime')
            : (int) $config->sessionExpiration;
    }

    protected function setSavePath(): void
    {
        if (empty($this->savePath)) {
            throw SessionException::forEmptySavepath();
        }

        if (preg_match('#(?:tcp://)?([^:?]+)(?:\:(\d+))?(\?.+)?#', $this->savePath, $matches)) {
            if (! isset($matches[3])) {
                $matches[3] = ''; // Just to avoid undefined index notices below
            }

            $this->savePath = [
                'host'     => $matches[1],
                'port'     => empty($matches[2]) ? self::DEFAULT_PORT : $matches[2],
                'password' => preg_match('#auth=([^\s&]+)#', $matches[3], $match) ? $match[1] : null,
                'database' => preg_match('#database=(\d+)#', $matches[3], $match) ? (int) $match[1] : 0,
                'timeout'  => preg_match('#timeout=(\d+\.\d+|\d+)#', $matches[3], $match) ? (float) $match[1] : 0.0,
            ];

            preg_match('#prefix=([^\s&]+)#', $matches[3], $match) && $this->keyPrefix = $match[1];
        } else {
            throw SessionException::forInvalidSavePathFormat($this->savePath);
        }
    }

    /**
     * Re-initialize existing session, or creates a new one.
     *
     * @param string $path The path where to store/retrieve the session
     * @param string $name The session name
     */
    public function open($path, $name): bool
    {
        if (empty($this->savePath)) {
            return false;
        }

        $redis = new Redis();

        if (! $redis->connect($this->savePath['host'], ($this->savePath['host'][0] === '/' ? 0 : $this->savePath['port']), $this->savePath['timeout'])) {
            $this->logger->error('Session: Unable to connect to Redis with the configured settings.');
        } elseif (isset($this->savePath['password']) && ! $redis->auth($this->savePath['password'])) {
            $this->logger->error('Session: Unable to authenticate to Redis instance.');
        } elseif (isset($this->savePath['database']) && ! $redis->select($this->savePath['database'])) {
            $this->logger->error('Session: Unable to select Redis database with index ' . $this->savePath['database']);
        } else {
            $this->redis = $redis;

            return true;
        }

        return false;
    }

    /**
     * Reads the session data from the session storage, and returns the results.
     *
     * @param string $id The session ID
     *
     * @return false|string Returns an encoded string of the read data.
     *                      If nothing was read, it must return false.
     */
    #[ReturnTypeWillChange]
    public function read($id)
    {
        if (isset($this->redis) && $this->lockSession($id)) {
            if (! isset($this->sessionID)) {
                $this->sessionID = $id;
            }

            $data = $this->redis->get($this->keyPrefix . $id);

            if (is_string($data)) {
                $this->keyExists = true;
            } else {
                $data = '';
            }

            $this->fingerprint = md5($data);

            return $data;
        }

        return '';
    }

    /**
     * Writes the session data to the session storage.
     *
     * @param string $id   The session ID
     * @param string $data The encoded session data
     */
    public function write($id, $data): bool
    {
        if (! isset($this->redis)) {
            return false;
        }

        if ($this->sessionID !== $id) {
            if (! $this->releaseLock() || ! $this->lockSession($id)) {
                return false;
            }

            $this->keyExists = false;
            $this->sessionID = $id;
        }

        if (isset($this->lockKey)) {
            $this->redis->expire($this->lockKey, 300);

            if ($this->fingerprint !== ($fingerprint = md5($data)) || $this->keyExists === false) {
                if ($this->redis->set($this->keyPrefix . $id, $data, $this->sessionExpiration)) {
                    $this->fingerprint = $fingerprint;
                    $this->keyExists   = true;

                    return true;
                }

                return false;
            }

            return $this->redis->expire($this->keyPrefix . $id, $this->sessionExpiration);
        }

        return false;
    }

    /**
     * Closes the current session.
     */
    public function close(): bool
    {
        if (isset($this->redis)) {
            try {
                $pingReply = $this->redis->ping();

                if (($pingReply === true) || ($pingReply === '+PONG')) {
                    if (isset($this->lockKey)) {
                        $this->redis->del($this->lockKey);
                    }

                    if (! $this->redis->close()) {
                        return false;
                    }
                }
            } catch (RedisException $e) {
                $this->logger->error('Session: Got RedisException on close(): ' . $e->getMessage());
            }

            $this->redis = null;

            return true;
        }

        return true;
    }

    /**
     * Destroys a session
     *
     * @param string $id The session ID being destroyed
     */
    public function destroy($id): bool
    {
        if (isset($this->redis, $this->lockKey)) {
            if (($result = $this->redis->del($this->keyPrefix . $id)) !== 1) {
                $this->logger->debug('Session: Redis::del() expected to return 1, got ' . var_export($result, true) . ' instead.');
            }

            return $this->destroyCookie();
        }

        return false;
    }

    /**
     * Cleans up expired sessions.
     *
     * @param int $max_lifetime Sessions that have not updated
     *                          for the last max_lifetime seconds will be removed.
     *
     * @return false|int Returns the number of deleted sessions on success, or false on failure.
     */
    #[ReturnTypeWillChange]
    public function gc($max_lifetime)
    {
        return 1;
    }

    /**
     * Acquires an emulated lock.
     *
     * @param string $sessionID Session ID
     */
    protected function lockSession(string $sessionID): bool
    {
        $lockKey = $this->keyPrefix . $sessionID . ':lock';

        // PHP 7 reuses the SessionHandler object on regeneration,
        // so we need to check here if the lock key is for the
        // correct session ID.
        if ($this->lockKey === $lockKey) {
            return $this->redis->expire($this->lockKey, 300);
        }

        $attempt = 0;

        do {
            if (($ttl = $this->redis->ttl($lockKey)) > 0) {
                sleep(1);

                continue;
            }

            if (! $this->redis->setex($lockKey, 300, (string) Time::now()->getTimestamp())) {
                $this->logger->error('Session: Error while trying to obtain lock for ' . $this->keyPrefix . $sessionID);

                return false;
            }

            $this->lockKey = $lockKey;
            break;
        } while (++$attempt < 30);

        if ($attempt === 30) {
            log_message('error', 'Session: Unable to obtain lock for ' . $this->keyPrefix . $sessionID . ' after 30 attempts, aborting.');

            return false;
        }

        if ($ttl === -1) {
            log_message('debug', 'Session: Lock for ' . $this->keyPrefix . $sessionID . ' had no TTL, overriding.');
        }

        $this->lock = true;

        return true;
    }

    /**
     * Releases a previously acquired lock
     */
    protected function releaseLock(): bool
    {
        if (isset($this->redis, $this->lockKey) && $this->lock) {
            if (! $this->redis->del($this->lockKey)) {
                $this->logger->error('Session: Error while trying to free lock for ' . $this->lockKey);

                return false;
            }

            $this->lockKey = null;
            $this->lock    = false;
        }

        return true;
    }
}
