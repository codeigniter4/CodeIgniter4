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

use CodeIgniter\Cache\CacheInterface;
use Config\App as AppConfig;

/**
 * This class allows you to use whatever your configured cache handler is for managing sessions. This helps minimize
 * configuration and also reduces the number of connections you establish to your cache.
 */
class ConfiguredCacheHandler extends BaseHandler
{
    /** @var CacheInterface|null  */
    protected $cache = null;

    /**
     * TODO make configurable via savePath? What should a savePath URI look like?
     *
     * @var string
     */
    protected $keyPrefix = 'ci_session_';

    /**
     * Number of seconds until the session ends.
     *
     * @var int
     */
    protected $sessionExpiration = 7200;

    /**
     * Lock key name
     *
     * @var string|null
     */
    protected $lockKey = null;

    /**
     * Key exists flag
     *
     * @var bool
     */
    protected $keyExists = false;

    public function __construct(AppConfig $config, string $ipAddress)
    {
        parent::__construct($config, $ipAddress);

        $this->sessionExpiration = empty($config->sessionExpiration)
            ? (int) ini_get('session.gc_maxlifetime')
            : (int) $config->sessionExpiration;

        if($this->matchIP === true) {
            $this->keyPrefix .= $this->ipAddress . '_';
        }
    }

    /**
     * Acquires an emulated lock.
     *
     * @param string $sessionID Session ID
     */
    protected function lockSession(string $sessionID): bool
    {
        $lock = $this->keyPrefix. $sessionID . '_sessionLock';

        if ($this->lockKey === $lock) {
            // This process owns this lock, re-up the expiration
            return $this->cache->save($this->lockKey, (string)time(), 300);
        }

        $attempt = 0;

        do {
            if(!empty($metadata = $this->cache->getMetaData($lock))) {
                // Another process has this lock, check if it has expired, if not, then wait a second.
                $ttl = $metadata['expire'] - time();

                if ($ttl > 0) {
                    sleep(1);
                    continue;
                }
            }

            // Either another process has expired or no other process has this lock. Create the lock for this process.
            if (! $this->cache->save($lock, (string)time(), 300)) {
                $this->logger->error('Session: Error while trying to obtain lock for ' . $sessionID);

                return false;
            }

            // Lock successfully established, save it for this process.
            $this->lockKey = $lock;
            break;
        } while (++$attempt < 30);

        if ($attempt === 30) {
            log_message('error', 'Session: Unable to obtain lock for ' . $sessionID . ' after 30 attempts, aborting.');

            return false;
        }

        $this->lock = true;

        return true;
    }

    /**
     * @inheritDoc
     */
    public function close()
    {
        $rtnVal = false;

        if (isset($this->cache)) {
            try {
                if(isset($this->lockKey)) {
                    $rtnVal = $this->cache->delete($this->lockKey);
                }
            } catch (\Throwable $t) {
                $this->logger->error('Session: Got Exception on close(): ' . $t->getMessage());
            }
        }

        return $rtnVal;
    }

    /**
     * @inheritDoc
     */
    public function destroy($id)
    {
        $rtnVal = false;
        if (isset($this->cache, $this->lockKey)) {
            // Destroy the lock for this session. Any other process can recreate this session and lock it.
            $this->cache->delete($this->lockKey);
            $rtnVal = $this->destroyCookie();
        }
        return $rtnVal;
    }

    /**
     * @inheritDoc
     */
    public function gc($max_lifetime)
    {
        return 1;
    }

    /**
     * @inheritDoc
     */
    public function open($path, $name): bool
    {
        try {
            $rtnVal = !empty($this->cache = cache());
        } catch (\Throwable $t) {
            $rtnVal = false;
        }
        return $rtnVal;
    }

    /**
     * @inheritDoc
     */
    public function read($id)
    {
        $data = '';
        if (isset($this->cache) && $this->lockSession($id)) {
            if (! isset($this->sessionID)) {
                $this->sessionID = $id;
            }

            $data = $this->cache->get($this->keyPrefix . $id);

            if (is_string($data)) {
                $this->keyExists = true;
            } else {
                $data = '';
            }

            $this->fingerprint = md5($data);
        }
        return $data;
    }

    public function releaseLock(): bool
    {
        if (isset($this->cache, $this->lockKey) && $this->lock) {
            if (! $this->cache->delete($this->lockKey)) {
                $this->logger->error('Session: Error while trying to free lock for ' . $this->lockKey);

                return false;
            }

            $this->lockKey = null;
            $this->lock    = false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function write($id, $data)
    {
        if (! isset($this->cache)) {
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
            $this->cache->save($this->lockKey, $data, $this->sessionExpiration);

            if ($this->fingerprint !== ($fingerprint = md5($data)) || $this->keyExists === false) {
                if ($this->cache->save($this->keyPrefix . $id, $data, $this->sessionExpiration)) {
                    $this->fingerprint = $fingerprint;
                    $this->keyExists   = true;

                    return true;
                }

                return false;
            }

            return $this->cache->save($this->keyPrefix . $id, $data, $this->sessionExpiration);
        }

        return false;
    }
}
