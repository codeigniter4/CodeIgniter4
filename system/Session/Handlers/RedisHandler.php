<?php

declare(strict_types=1);

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
use Config\Session as SessionConfig;
use Redis;
use RedisException;
use ReturnTypeWillChange;

/**
 * Session handler using Redis for persistence
 */
class RedisHandler extends BaseHandler
{
    private const DEFAULT_PORT     = 6379;
    private const DEFAULT_PROTOCOL = 'tcp';

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
     * Time (microseconds) to wait if lock cannot be acquired.
     */
    private int $lockRetryInterval = 100_000;

    /**
     * Maximum number of lock acquisition attempts.
     */
    private int $lockMaxRetries = 300;

    /**
     * @param string $ipAddress User's IP address
     *
     * @throws SessionException
     */
    public function __construct(SessionConfig $config, string $ipAddress)
    {
        parent::__construct($config, $ipAddress);

        // Store Session configurations
        $this->sessionExpiration = ($config->expiration === 0)
            ? (int) ini_get('session.gc_maxlifetime')
            : $config->expiration;

        // Add sessionCookieName for multiple session cookies.
        $this->keyPrefix .= $config->cookieName . ':';

        $this->setSavePath();

        if ($this->matchIP === true) {
            $this->keyPrefix .= $this->ipAddress . ':';
        }

        $this->lockRetryInterval = $config->lockWait ?? $this->lockRetryInterval;
        $this->lockMaxRetries    = $config->lockAttempts ?? $this->lockMaxRetries;
    }

    protected function setSavePath(): void
    {
        if (empty($this->savePath)) {
            throw SessionException::forEmptySavepath();
        }

        $url   = parse_url($this->savePath);
        $query = [];

        if ($url === false) {
            // Unix domain socket like `unix:///var/run/redis/redis.sock?persistent=1`.
            if (preg_match('#unix://(/[^:?]+)(\?.+)?#', $this->savePath, $matches)) {
                $host = $matches[1];
                $port = 0;

                if (isset($matches[2])) {
                    parse_str(ltrim($matches[2], '?'), $query);
                }
            } else {
                throw SessionException::forInvalidSavePathFormat($this->savePath);
            }
        } else {
            // Also accepts `/var/run/redis.sock` for backward compatibility.
            if (isset($url['path']) && $url['path'][0] === '/') {
                $host = $url['path'];
                $port = 0;
            } else {
                // TCP connection.
                if (! isset($url['host'])) {
                    throw SessionException::forInvalidSavePathFormat($this->savePath);
                }

                $protocol = $url['scheme'] ?? self::DEFAULT_PROTOCOL;
                $host     = $protocol . '://' . $url['host'];
                $port     = $url['port'] ?? self::DEFAULT_PORT;
            }

            if (isset($url['query'])) {
                parse_str($url['query'], $query);
            }
        }

        $password = $query['auth'] ?? null;
        $database = isset($query['database']) ? (int) $query['database'] : 0;
        $timeout  = isset($query['timeout']) ? (float) $query['timeout'] : 0.0;
        $prefix   = $query['prefix'] ?? null;

        $this->savePath = [
            'host'     => $host,
            'port'     => $port,
            'password' => $password,
            'database' => $database,
            'timeout'  => $timeout,
        ];

        if ($prefix !== null) {
            $this->keyPrefix = $prefix;
        }
    }

    /**
     * Re-initialize existing session, or creates a new one.
     *
     * @param string $path The path where to store/retrieve the session
     * @param string $name The session name
     *
     * @throws RedisException
     */
    public function open($path, $name): bool
    {
        if (empty($this->savePath)) {
            return false;
        }

        $redis = new Redis();

        if (
            ! $redis->connect(
                $this->savePath['host'],
                $this->savePath['port'],
                $this->savePath['timeout'],
            )
        ) {
            $this->logger->error('Session: Unable to connect to Redis with the configured settings.');
        } elseif (isset($this->savePath['password']) && ! $redis->auth($this->savePath['password'])) {
            $this->logger->error('Session: Unable to authenticate to Redis instance.');
        } elseif (isset($this->savePath['database']) && ! $redis->select($this->savePath['database'])) {
            $this->logger->error(
                'Session: Unable to select Redis database with index ' . $this->savePath['database'],
            );
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
     *
     * @throws RedisException
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

        return false;
    }

    /**
     * Writes the session data to the session storage.
     *
     * @param string $id   The session ID
     * @param string $data The encoded session data
     *
     * @throws RedisException
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
                    if (isset($this->lockKey) && ! $this->releaseLock()) {
                        return false;
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
     *
     * @throws RedisException
     */
    public function destroy($id): bool
    {
        if (isset($this->redis, $this->lockKey)) {
            if (($result = $this->redis->del($this->keyPrefix . $id)) !== 1) {
                $this->logger->debug(
                    'Session: Redis::del() expected to return 1, got ' . var_export($result, true) . ' instead.',
                );
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
     *
     * @throws RedisException
     */
    protected function lockSession(string $sessionID): bool
    {
        $lockKey = $this->keyPrefix . $sessionID . ':lock';

        // PHP 7 reuses the SessionHandler object on regeneration,
        // so we need to check here if the lock key is for the
        // correct session ID.
        if ($this->lockKey === $lockKey) {
            // If there is the lock, make the ttl longer.
            return $this->redis->expire($this->lockKey, 300);
        }

        $attempt = 0;

        do {
            $result = $this->redis->set(
                $lockKey,
                (string) Time::now()->getTimestamp(),
                // NX -- Only set the key if it does not already exist.
                // EX seconds -- Set the specified expire time, in seconds.
                ['nx', 'ex' => 300],
            );

            if (! $result) {
                usleep($this->lockRetryInterval);

                continue;
            }

            $this->lockKey = $lockKey;
            break;
        } while (++$attempt < $this->lockMaxRetries);

        if ($attempt === 300) {
            $this->logger->error(
                'Session: Unable to obtain lock for ' . $this->keyPrefix . $sessionID
                . ' after 300 attempts, aborting.',
            );

            return false;
        }

        $this->lock = true;

        return true;
    }

    /**
     * Releases a previously acquired lock
     *
     * @throws RedisException
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
