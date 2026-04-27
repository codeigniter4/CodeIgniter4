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

namespace CodeIgniter\Test\Mock;

use CodeIgniter\Cache\LockStoreInterface;
use CodeIgniter\I18n\Time;

class MockLockStore implements LockStoreInterface
{
    /**
     * @var array<string, array{owner: string, expires: int}>
     */
    private array $locks = [];

    public function acquireLock(string $key, string $owner, int $ttl): bool
    {
        if ($this->getLockOwner($key) !== null) {
            return false;
        }

        $this->locks[$key] = [
            'owner'   => $owner,
            'expires' => Time::now()->getTimestamp() + $ttl,
        ];

        return true;
    }

    public function releaseLock(string $key, string $owner): bool
    {
        if ($this->getLockOwner($key) !== $owner) {
            return false;
        }

        unset($this->locks[$key]);

        return true;
    }

    public function forceReleaseLock(string $key): bool
    {
        unset($this->locks[$key]);

        return true;
    }

    public function refreshLock(string $key, string $owner, int $ttl): bool
    {
        if ($this->getLockOwner($key) !== $owner) {
            return false;
        }

        $this->locks[$key]['expires'] = Time::now()->getTimestamp() + $ttl;

        return true;
    }

    public function getLockOwner(string $key): ?string
    {
        if (! isset($this->locks[$key])) {
            return null;
        }

        if ($this->locks[$key]['expires'] <= Time::now()->getTimestamp()) {
            unset($this->locks[$key]);

            return null;
        }

        return $this->locks[$key]['owner'];
    }

    public function clean(): void
    {
        $this->locks = [];
    }
}
