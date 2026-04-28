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

namespace CodeIgniter\Lock;

use CodeIgniter\Cache\CacheFactory;
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\I18n\Time;
use CodeIgniter\Lock\Exceptions\LockException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Cache;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class LockTest extends CIUnitTestCase
{
    private Cache $config;
    private LockManager $locks;

    protected function setUp(): void
    {
        parent::setUp();

        helper('filesystem');

        $this->config                    = new Cache();
        $this->config->file['storePath'] = WRITEPATH . 'cache/LockTest';

        if (! is_dir($this->config->file['storePath'])) {
            mkdir($this->config->file['storePath'], 0777, true);
        }

        $this->locks = new LockManager(CacheFactory::getHandler($this->config, 'file'));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Time::setTestNow();

        if (is_dir($this->config->file['storePath'])) {
            delete_files($this->config->file['storePath'], false, true);
            rmdir($this->config->file['storePath']);
        }
    }

    public function testLockCanBeAcquiredAndReleased(): void
    {
        $lock = $this->locks->create('reports.daily-export', 60);

        $this->assertTrue($lock->acquire());
        $this->assertFileExists($this->lockFile('reports.daily-export'));
        $this->assertTrue($lock->isAcquired());
        $this->assertTrue($lock->release());
        $this->assertFalse($lock->isAcquired());
        $this->assertTrue($this->locks->create('reports.daily-export', 60)->acquire());
    }

    public function testCompetingLockCannotBeAcquiredUntilReleased(): void
    {
        $first  = $this->locks->create('reports.daily-export', 60);
        $second = $this->locks->create('reports.daily-export', 60);

        $this->assertTrue($first->acquire());
        $this->assertFalse($second->acquire());

        $this->assertTrue($first->release());
        $this->assertTrue($second->acquire());
    }

    public function testSameLockCannotBeAcquiredTwice(): void
    {
        $lock = $this->locks->create('reports.daily-export', 60);

        $this->assertTrue($lock->acquire());
        $this->assertFalse($lock->acquire());
    }

    public function testExpiredLockCanBeAcquiredByNewOwner(): void
    {
        Time::setTestNow('2026-01-01 12:00:00');

        $first = $this->locks->create('imports.customer-feed', 10);

        $this->assertTrue($first->acquire());

        Time::setTestNow('2026-01-01 12:00:11');

        $second = $this->locks->create('imports.customer-feed', 10);

        $this->assertTrue($second->acquire());
        $this->assertFalse($first->isAcquired());
    }

    public function testOnlyOwnerCanReleaseLock(): void
    {
        $first  = $this->locks->create('payments.settlement', 60);
        $second = $this->locks->create('payments.settlement', 60);

        $this->assertTrue($first->acquire());
        $this->assertFalse($second->release());
        $this->assertTrue($first->isAcquired());
    }

    public function testForceReleaseIgnoresOwner(): void
    {
        $first  = $this->locks->create('payments.settlement', 60);
        $second = $this->locks->create('payments.settlement', 60);

        $this->assertTrue($first->acquire());
        $this->assertTrue($second->forceRelease());
        $this->assertTrue($second->acquire());
    }

    public function testRestoreCanReleaseOwnedLock(): void
    {
        $lock = $this->locks->create('jobs.unique', 60);

        $this->assertTrue($lock->acquire());

        $restored = $this->locks->restore('jobs.unique', $lock->owner(), 60);

        $this->assertTrue($restored->isAcquired());
        $this->assertTrue($restored->release());
        $this->assertFalse($lock->isAcquired());
    }

    public function testRefreshRequiresOwner(): void
    {
        $first  = $this->locks->create('cache.rebuild', 60);
        $second = $this->locks->create('cache.rebuild', 60);

        $this->assertTrue($first->acquire());
        $this->assertTrue($first->refresh(120));
        $this->assertFalse($second->refresh(120));
    }

    public function testRunReleasesLockAfterCallback(): void
    {
        $lock = $this->locks->create('notifications.send', 60);

        $this->assertSame('sent', $lock->run(static fn (): string => 'sent'));
        $this->assertTrue($this->locks->create('notifications.send', 60)->acquire());
    }

    public function testRunReturnsFalseWhenLockCannotBeAcquired(): void
    {
        $first  = $this->locks->create('notifications.send', 60);
        $second = $this->locks->create('notifications.send', 60);

        $this->assertTrue($first->acquire());
        $this->assertFalse($second->run(static fn (): string => 'sent'));
    }

    public function testLogicalNamesCanContainReservedCacheCharacters(): void
    {
        $lock = $this->locks->create('tenant:1/payments/{settlement}', 60);

        $this->assertTrue($lock->acquire());
    }

    public function testEmptyLockNameIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Lock name cannot be empty.');

        $this->locks->create('');
    }

    public function testNonPositiveTtlIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Lock TTL must be a positive integer.');

        $this->locks->create('reports.daily-export', 0);
    }

    public function testUnsupportedCacheHandlerThrows(): void
    {
        $this->expectException(LockException::class);
        $this->expectExceptionMessage('does not support locks');

        // @phpstan-ignore argument.type
        new LockManager(CacheFactory::getHandler($this->config, 'dummy'));
    }

    private function lockFile(string $name): string
    {
        return rtrim($this->config->file['storePath'], '\\/') . '/lock_' . hash('xxh128', $name);
    }
}
