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

namespace CodeIgniter\Database\Live\MySQLi;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use mysqli;
use PHPUnit\Framework\Attributes\Group;
use Throwable;

/**
 * InnoDB errno 1213 rolls the victim transaction back. These tests lock two
 * rows in opposite orders so the CodeIgniter connection is that victim.
 *
 * Errno 1205 is a different outcome and is not a passing result here.
 *
 * @internal
 */
#[Group('DatabaseLive')]
final class DeadlockTransactionTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate              = false;
    private ?BaseConnection $waiter = null;
    private ?mysqli $held           = null;
    private ?string $probeTable     = null;

    protected function setUp(): void
    {
        $config = config('Database');

        if ($config->tests['DBDriver'] !== 'MySQLi') {
            $this->markTestSkipped('Only MySQLi can complete this test.');
        }

        $this->db = Database::connect($this->DBGroup, false);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->releaseDeadlock();

        parent::tearDown();
    }

    public function testFollowingWriteDoesNotAutocommitAndTransCommitReturnsFalse(): void
    {
        $this->assertDeadlockVictimDoesNotPublishTheNextWrite(false);
    }

    public function testFollowingWriteDoesNotAutocommitAndTransCompleteRollsItBack(): void
    {
        $this->assertDeadlockVictimDoesNotPublishTheNextWrite(true);
    }

    private function assertDeadlockVictimDoesNotPublishTheNextWrite(bool $useComplete): void
    {
        $table            = $this->db->escapeIdentifier($this->db->prefixTable('deadlock_probe'));
        $this->probeTable = $table;
        $this->forceDeadlock($table);

        $this->assertSame(1213, (int) $this->db->error()['code']);
        $this->assertSame(1, $this->db->transDepth);

        $this->db->query('INSERT INTO ' . $table . " (id, note) VALUES (102, 'after')");

        $visibleBefore = $this->committedIds($table);
        $finished      = $useComplete ? $this->db->transComplete() : $this->db->transCommit();
        $visibleAfter  = $this->committedIds($table);

        $this->assertSame(
            ['visible_before' => [], 'finished' => false, 'visible_after' => []],
            [
                'visible_before' => $visibleBefore,
                'finished'       => $finished,
                'visible_after'  => $visibleAfter,
            ],
        );
    }

    /**
     * Make $this->db the InnoDB deadlock victim. The victim row is id 101.
     */
    private function forceDeadlock(string $table): void
    {
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $this->releaseDeadlock();
            $this->resetProbe($table);

            $this->waiter = Database::connect($this->DBGroup, false);
            $this->waiter->initialize();
            $this->waiter->query('SET SESSION innodb_lock_wait_timeout = 3');
            $this->waiter->transStart();

            for ($i = 0; $i < 20; $i++) {
                $this->waiter->query('UPDATE ' . $table . " SET note = 'w{$i}' WHERE id = 1");
            }

            $held = $this->waiter->getConnection();
            $this->assertInstanceOf(mysqli::class, $held);
            $this->held = $held;

            // The victim has to lock row 2 before the waiter asks for it.
            // Sending that request first lets the waiter take row 2, and the
            // victim then waits out innodb_lock_wait_timeout (errno 1205).
            $this->db->query('SET SESSION innodb_lock_wait_timeout = 3');
            $this->db->transException(false);
            $this->db->transStart();
            $this->db->query('INSERT INTO ' . $table . " (id, note) VALUES (101, 'victim')");
            $this->db->query('UPDATE ' . $table . " SET note = 'held' WHERE id = 2");

            $this->held->query('UPDATE ' . $table . " SET note = 'waiting' WHERE id = 2", MYSQLI_ASYNC);
            usleep(400000);

            $this->db->query('UPDATE ' . $table . " SET note = 'closing' WHERE id = 1");

            if ((int) $this->db->error()['code'] === 1213) {
                return;
            }
        }

        $this->fail('Precondition: InnoDB did not report errno 1213 on the CodeIgniter connection.');
    }

    private function resetProbe(string $table): void
    {
        if ($this->db->transDepth > 0) {
            $this->db->transRollback();
        }

        $this->db->query('DROP TABLE IF EXISTS ' . $table);
        $this->db->query(
            'CREATE TABLE ' . $table . ' ('
            . 'id INT NOT NULL, '
            . 'note VARCHAR(32) NOT NULL, '
            . 'PRIMARY KEY (id)'
            . ') ENGINE=InnoDB',
        );
        $this->db->query('INSERT INTO ' . $table . " (id, note) VALUES (1, 'one'), (2, 'two')");
    }

    /**
     * @return list<int>
     */
    private function committedIds(string $table): array
    {
        $observer = Database::connect($this->DBGroup, false);
        $observer->initialize();
        $rows = $observer->query('SELECT id FROM ' . $table . ' WHERE id IN (101, 102) ORDER BY id')->getResultArray();
        $observer->close();

        $ids = [];

        foreach ($rows as $row) {
            $ids[] = (int) $row['id'];
        }

        return $ids;
    }

    private function releaseDeadlock(): void
    {
        if ($this->held instanceof mysqli) {
            try {
                $this->held->reap_async_query();
            } catch (Throwable) {
                // The waiting statement already finished, or the socket is closed.
            }

            $this->held = null;
        }

        if ($this->waiter instanceof BaseConnection) {
            if ($this->waiter->transDepth > 0) {
                $this->waiter->transRollback();
            }

            $this->waiter->close();
            $this->waiter = null;
        }

        if ($this->db instanceof BaseConnection && $this->db->transDepth > 0) {
            $this->db->transRollback();
        }

        if ($this->probeTable !== null && $this->db instanceof BaseConnection) {
            $this->db->query('DROP TABLE IF EXISTS ' . $this->probeTable);
            $this->probeTable = null;
        }
    }
}
