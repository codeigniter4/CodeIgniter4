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

namespace CodeIgniter\Session\Handlers\Database;

use CodeIgniter\Events\Events;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use Config\Session as SessionConfig;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class PostgreHandlerMockTest extends CIUnitTestCase
{
    private const IP_ADDRESS = "192.168.1.1' OR '1'='1";
    private const SESSION_ID = "sess'id; DROP TABLE users; --";

    private function createHandler(bool $matchIP, MockConnection $mockDb): PostgreHandler
    {
        $config           = new SessionConfig();
        $config->driver   = PostgreHandler::class;
        $config->savePath = 'ci_sessions';
        $config->matchIP  = $matchIP;

        $handler = new PostgreHandler($config, self::IP_ADDRESS);
        $this->setPrivateProperty($handler, 'db', $mockDb);

        return $handler;
    }

    private function createMockDb(): PostgreHandlerMockConnection
    {
        return new PostgreHandlerMockConnection(['DBDriver' => 'Postgre']);
    }

    public function testLockAndReleaseSessionWithoutMatchIP(): void
    {
        $db      = $this->createMockDb();
        $handler = $this->createHandler(false, $db);

        $queryCount = 0;
        $listener   = static function () use (&$queryCount): void {
            $queryCount++;
        };
        Events::on('DBQuery', $listener);

        $lockSession = self::getPrivateMethodInvoker($handler, 'lockSession');
        $releaseLock = self::getPrivateMethodInvoker($handler, 'releaseLock');

        $this->assertTrue($lockSession(self::SESSION_ID));
        $this->assertSame(
            "hashtext('sess''id; DROP TABLE users; --')",
            self::getPrivateProperty($handler, 'lock'),
        );

        $this->assertTrue($releaseLock());
        $this->assertFalse(self::getPrivateProperty($handler, 'lock'));

        Events::removeListener('DBQuery', $listener);

        $this->assertSame(0, $queryCount);
        $this->assertSame([
            "SELECT pg_advisory_lock(hashtext('sess''id; DROP TABLE users; --'))",
            "SELECT pg_advisory_unlock(hashtext('sess''id; DROP TABLE users; --'))",
        ], $db->executed);

        $this->assertStringNotContainsString(self::SESSION_ID, $db->executed[0]);
        $this->assertStringNotContainsString(self::SESSION_ID, $db->executed[1]);
    }

    public function testLockAndReleaseSessionWithMatchIP(): void
    {
        $db      = $this->createMockDb();
        $handler = $this->createHandler(true, $db);

        $queryCount = 0;
        $listener   = static function () use (&$queryCount): void {
            $queryCount++;
        };
        Events::on('DBQuery', $listener);

        $lockSession = self::getPrivateMethodInvoker($handler, 'lockSession');
        $releaseLock = self::getPrivateMethodInvoker($handler, 'releaseLock');

        $this->assertTrue($lockSession(self::SESSION_ID));
        $this->assertSame(
            "hashtext('sess''id; DROP TABLE users; --'), hashtext('192.168.1.1'' OR ''1''=''1')",
            self::getPrivateProperty($handler, 'lock'),
        );

        $this->assertTrue($releaseLock());
        $this->assertFalse(self::getPrivateProperty($handler, 'lock'));

        Events::removeListener('DBQuery', $listener);

        $this->assertSame(0, $queryCount);
        $this->assertSame([
            "SELECT pg_advisory_lock(hashtext('sess''id; DROP TABLE users; --'), hashtext('192.168.1.1'' OR ''1''=''1'))",
            "SELECT pg_advisory_unlock(hashtext('sess''id; DROP TABLE users; --'), hashtext('192.168.1.1'' OR ''1''=''1'))",
        ], $db->executed);

        $this->assertStringNotContainsString(self::SESSION_ID, $db->executed[0]);
        $this->assertStringNotContainsString(self::IP_ADDRESS, $db->executed[0]);
    }
}
