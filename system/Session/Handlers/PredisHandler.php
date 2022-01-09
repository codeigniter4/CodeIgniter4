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

use CodeIgniter\Session\Exceptions\SessionException;
use Config\App as AppConfig;
use Predis\Client;
use Predis\PredisException;
use ReturnTypeWillChange;
use Throwable;

/**
 * Session handler using Predis for persistence
 */
class PredisHandler extends BaseHandler
{
    /**
     * phpRedis instance
     *
     * @var Client|null
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
     * Initialize connection to redis server
     */
    private function connectToRedisServer()
    {
        $parameters = [];
        if (isset($this->savePath['password'])) {
            $parameters['password'] = $this->savePath['password'];
        }
        if (isset($this->savePath['database'])) {
            $parameters['database'] = $this->savePath['database'];
        }

        $this->redis = new Client($this->savePath, $parameters);

        $this->redis->time();
    }

    /**
     * Initialize connection to redis cluster
     */
    private function connectToRedisCluster()
    {
        $cluster    = ["{$this->savePath['host']}:{$this->savePath['port']}"];
        $timeout    = $this->savePath['timeout'] ?? 0;
        $parameters = [
            'read_write_timeout' => $timeout,
            'timeout'            => $timeout,
            'username'           => $this->savePath['username'],
            'password'           => $this->savePath['password'],
            'prefix'             => $this->keyPrefix,
            'persistent'         => $this->savePath['persistent'],
        ];
        $this->redis = new Client($cluster, ['cluster' => 'redis', 'parameters' => $parameters]);
        // ping(), time(), etc. are not supported for predis cluster mode, so try to grab a key to check connectivity.
        $this->redis->get('testkey');
    }

    /**
     * Create PredisHandler instance
     *
     * @throws SessionException
     */
    public function __construct(AppConfig $config, string $ipAddress)
    {
        parent::__construct($config, $ipAddress);

        if (empty($this->savePath)) {
            throw SessionException::forEmptySavepath();
        }

        // TODO: add TLS compatibility
        if (preg_match('#(?:tcp://)?([^:?]+)(?:\:(\d+))?(\?.+)?#', $this->savePath, $matches)) {
            if (! isset($matches[3])) {
                $matches[3] = ''; // Just to avoid undefined index notices below
            }

            $this->savePath = [
                'host'     => $matches[1],
                'port'     => empty($matches[2]) ? 6379 : $matches[2],
                'password' => preg_match('#(password|auth)=([^\s&]+)#', $matches[3], $match) ? $match[2] : null,
                'username' => preg_match('#username=([^\s&]+)#', $matches[3], $match) ? $match[1] : null,
                'database' => preg_match('#database=(\d+)#', $matches[3], $match) ? (int) $match[1] : null,
                'timeout'  => preg_match('#timeout=(\d+\.\d+)#', $matches[3], $match) ? (float) $match[1] : null,
                // Accept a value of 'true' or > 0 to enable cluster mode
                'isCluster'  => preg_match('#isCluster=([^\s&]+)#', $matches[3], $match) ? ($match[1] === 'true' || $match[1] > 0) : null,
                'persistent' => preg_match('#persistent=([^\s&]+)#', $matches[3], $match) ? ($match[1] === 'true' || $match[1] > 0) : null,
            ];

            preg_match('#prefix=([^\s&]+)#', $matches[3], $match) && $this->keyPrefix = $match[1];
        } else {
            throw SessionException::forInvalidSavePathFormat($this->savePath);
        }

        if ($this->matchIP === true) {
            $this->keyPrefix .= $this->ipAddress . ':';
        }

        $this->sessionExpiration = empty($config->sessionExpiration)
            ? (int) ini_get('session.gc_maxlifetime')
            : (int) $config->sessionExpiration;
    }

    /**
     * {@inheritDoc}
     */
    public function open($path, $name): bool
    {
        $rtnVal = false;
        if (! empty($this->savePath)) {
            try {
                if ($this->savePath['isCluster']) {
                    $this->connectToRedisCluster();
                } else {
                    $this->connectToRedisServer();
                }
                $rtnVal = true;
            } catch (Throwable $t) {
                $this->logger->error("Session: Unable to connect to redis: {$t->getMessage()}");
            }
        }

        return $rtnVal;
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function read($id)
    {
        $rtnVal = '';

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

            $rtnVal = $data;
        }

        return $rtnVal;
    }

    /**
     * {@inheritDoc}
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
                if ($this->redis->setex($this->keyPrefix . $id, $this->sessionExpiration, $data)) {
                    $this->fingerprint = $fingerprint;
                    $this->keyExists   = true;

                    return true;
                }

                return false;
            }

            return (bool) ($this->redis->expire($this->keyPrefix . $id, $this->sessionExpiration));
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function close(): bool
    {
        if (isset($this->redis)) {
            try {
                if (isset($this->lockKey)) {
                    $this->redis->del($this->lockKey);
                }

                $this->redis->disconnect();
            } catch (PredisException $e) {
                $this->logger->error('Session: Got PredisException on close(): ' . $e->getMessage());
            }

            $this->redis = null;

            return true;
        }

        return true;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function gc($max_lifetime)
    {
        return 1;
    }

    /**
     * {@inheritDoc}
     */
    protected function lockSession(string $sessionID): bool
    {
        // PHP 7 reuses the SessionHandler object on regeneration,
        // so we need to check here if the lock key is for the
        // correct session ID.
        if ($this->lockKey === $this->keyPrefix . $sessionID . ':lock') {
            return (bool) ($this->redis->expire($this->lockKey, 300));
        }

        $lockKey = $this->keyPrefix . $sessionID . ':lock';
        $attempt = 0;

        do {
            if (($ttl = $this->redis->ttl($lockKey)) > 0) {
                sleep(1);

                continue;
            }

            if (! $this->redis->setex($lockKey, 300, (string) time())) {
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
     * {@inheritDoc}
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
