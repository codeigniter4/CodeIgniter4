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
use Memcached;
use ReturnTypeWillChange;

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
     * @var int
     */
    protected $sessionExpiration = 7200;

    /**
     * @throws SessionException
     */
    public function __construct(AppConfig $config, string $ipAddress)
    {
        parent::__construct($config, $ipAddress);

        if (empty($this->savePath)) {
            throw SessionException::forEmptySavepath();
        }

        if ($this->matchIP === true) {
            $this->keyPrefix .= $this->ipAddress . ':';
        }

        if (! empty($this->keyPrefix)) {
            ini_set('memcached.sess_prefix', $this->keyPrefix);
        }

        $this->sessionExpiration = $config->sessionExpiration;
    }

    /**
     * Re-initialize existing session, or creates a new one.
     *
     * @param string $path The path where to store/retrieve the session
     * @param string $name The session name
     */
    public function open($path, $name): bool
    {
        $this->memcached = new Memcached();
        $this->memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true); // required for touch() usage

        $serverList = [];

        foreach ($this->memcached->getServerList() as $server) {
            $serverList[] = $server['host'] . ':' . $server['port'];
        }

        if (! preg_match_all('#,?([^,:]+)\:(\d{1,5})(?:\:(\d+))?#', $this->savePath, $matches, PREG_SET_ORDER)) {
            $this->memcached = null;
            $this->logger->error('Session: Invalid Memcached save path format: ' . $this->savePath);

            return false;
        }

        foreach ($matches as $match) {
            // If Memcached already has this server (or if the port is invalid), skip it
            if (in_array($match[1] . ':' . $match[2], $serverList, true)) {
                $this->logger->debug('Session: Memcached server pool already has ' . $match[1] . ':' . $match[2]);

                continue;
            }

            if (! $this->memcached->addServer($match[1], (int) $match[2], $match[3] ?? 0)) {
                $this->logger->error('Could not add ' . $match[1] . ':' . $match[2] . ' to Memcached server pool.');
            } else {
                $serverList[] = $match[1] . ':' . $match[2];
            }
        }

        if (empty($serverList)) {
            $this->logger->error('Session: Memcached server pool is empty.');

            return false;
        }

        return true;
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
        if (isset($this->memcached) && $this->lockSession($id)) {
            if (! isset($this->sessionID)) {
                $this->sessionID = $id;
            }

            $data = (string) $this->memcached->get($this->keyPrefix . $id);

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
        if (! isset($this->memcached)) {
            return false;
        }

        if ($this->sessionID !== $id) {
            if (! $this->releaseLock() || ! $this->lockSession($id)) {
                return false;
            }

            $this->fingerprint = md5('');
            $this->sessionID   = $id;
        }

        if (isset($this->lockKey)) {
            $this->memcached->replace($this->lockKey, time(), 300);

            if ($this->fingerprint !== ($fingerprint = md5($data))) {
                if ($this->memcached->set($this->keyPrefix . $id, $data, $this->sessionExpiration)) {
                    $this->fingerprint = $fingerprint;

                    return true;
                }

                return false;
            }

            return $this->memcached->touch($this->keyPrefix . $id, $this->sessionExpiration);
        }

        return false;
    }

    /**
     * Closes the current session.
     */
    public function close(): bool
    {
        if (isset($this->memcached)) {
            if (isset($this->lockKey)) {
                $this->memcached->delete($this->lockKey);
            }

            if (! $this->memcached->quit()) {
                return false;
            }

            $this->memcached = null;

            return true;
        }

        return false;
    }

    /**
     * Destroys a session
     *
     * @param string $id The session ID being destroyed
     */
    public function destroy($id): bool
    {
        if (isset($this->memcached, $this->lockKey)) {
            $this->memcached->delete($this->keyPrefix . $id);

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
        if (isset($this->lockKey)) {
            return $this->memcached->replace($this->lockKey, time(), 300);
        }

        $lockKey = $this->keyPrefix . $sessionID . ':lock';
        $attempt = 0;

        do {
            if ($this->memcached->get($lockKey)) {
                sleep(1);

                continue;
            }

            if (! $this->memcached->set($lockKey, time(), 300)) {
                $this->logger->error('Session: Error while trying to obtain lock for ' . $this->keyPrefix . $sessionID);

                return false;
            }

            $this->lockKey = $lockKey;
            break;
        } while (++$attempt < 30);

        if ($attempt === 30) {
            $this->logger->error('Session: Unable to obtain lock for ' . $this->keyPrefix . $sessionID . ' after 30 attempts, aborting.');

            return false;
        }

        $this->lock = true;

        return true;
    }

    /**
     * Releases a previously acquired lock
     */
    protected function releaseLock(): bool
    {
        if (isset($this->memcached, $this->lockKey) && $this->lock) {
            if (
                ! $this->memcached->delete($this->lockKey)
                && $this->memcached->getResultCode() !== Memcached::RES_NOTFOUND
            ) {
                $this->logger->error('Session: Error while trying to free lock for ' . $this->lockKey);

                return false;
            }

            $this->lockKey = null;
            $this->lock    = false;
        }

        return true;
    }
}
