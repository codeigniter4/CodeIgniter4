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

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use CodeIgniter\Test\Mock\MockResult;
use Config\Session as SessionConfig;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class PostgreHandlerMockTest extends CIUnitTestCase
{
    private function createHandler(bool $matchIP, object $mockDb): PostgreHandler
    {
        $config           = new SessionConfig();
        $config->driver   = PostgreHandler::class;
        $config->savePath = 'ci_sessions';
        $config->matchIP  = $matchIP;

        $handler = new PostgreHandler($config, "192.168.1.1' OR '1'='1");
        $this->setPrivateProperty($handler, 'db', $mockDb);

        return $handler;
    }

    private function createMockDb(): object
    {
        return new class (['DBDriver' => 'Postgre']) extends MockConnection {
            /**
             * @var list<array{sql: string, binds: array<array-key, mixed>|null}>
             */
            public array $executed = [];

            public function query(string $sql, $binds = null, bool $setEscapeFlags = true, string $queryClass = ''): MockResult
            {
                $this->executed[] = [
                    'sql'   => $sql,
                    'binds' => is_array($binds) ? $binds : null,
                ];

                return new MockResult($this->connID, $this->resultID);
            }
        };
    }

    public function testLockAndReleaseSessionWithoutMatchIP(): void
    {
        $db      = $this->createMockDb();
        $handler = $this->createHandler(false, $db);

        $lockSession = self::getPrivateMethodInvoker($handler, 'lockSession');
        $releaseLock = self::getPrivateMethodInvoker($handler, 'releaseLock');

        $sessionID = "sess'id; DROP TABLE users; --";
        $this->assertTrue($lockSession($sessionID));
        $this->assertTrue($releaseLock());

        $this->assertCount(2, $db->executed);

        $this->assertSame('SELECT pg_advisory_lock(hashtext(?))', $db->executed[0]['sql']);
        $this->assertSame([$sessionID], $db->executed[0]['binds']);

        $this->assertSame('SELECT pg_advisory_unlock(hashtext(?))', $db->executed[1]['sql']);
        $this->assertSame([$sessionID], $db->executed[1]['binds']);
    }

    public function testLockAndReleaseSessionWithMatchIP(): void
    {
        $db      = $this->createMockDb();
        $handler = $this->createHandler(true, $db);

        $lockSession = self::getPrivateMethodInvoker($handler, 'lockSession');
        $releaseLock = self::getPrivateMethodInvoker($handler, 'releaseLock');

        $sessionID = "sess'id; DROP TABLE users; --";
        $this->assertTrue($lockSession($sessionID));
        $this->assertTrue($releaseLock());

        $this->assertCount(2, $db->executed);

        $this->assertSame('SELECT pg_advisory_lock(hashtext(?), hashtext(?))', $db->executed[0]['sql']);
        $this->assertSame([$sessionID, "192.168.1.1' OR '1'='1"], $db->executed[0]['binds']);

        $this->assertSame('SELECT pg_advisory_unlock(hashtext(?), hashtext(?))', $db->executed[1]['sql']);
        $this->assertSame([$sessionID, "192.168.1.1' OR '1'='1"], $db->executed[1]['binds']);
    }
}
