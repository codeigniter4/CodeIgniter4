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

namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class TransactionCallbacksTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    protected function setUp(): void
    {
        // Reset connection instance.
        $this->db = Database::connect($this->DBGroup, false);

        parent::setUp();
    }

    public function testAfterCommitRunsImmediatelyWhenNoTransactionIsActive(): void
    {
        $callbacks = [];

        $result = $this->db->afterCommit(static function () use (&$callbacks): void {
            $callbacks[] = 'ran';
        });

        $this->assertSame($this->db, $result);
        $this->assertSame(['ran'], $callbacks);
    }

    public function testAfterCommitRunsAfterSuccessfulTransactionCommit(): void
    {
        $callbacks = [];

        $this->db->transStart();
        $this->db->afterCommit(static function () use (&$callbacks): void {
            $callbacks[] = 'committed';
        });

        $this->assertSame([], $callbacks);

        $this->db->transComplete();

        $this->assertSame(['committed'], $callbacks);
    }

    public function testAfterCommitRunsAfterManualTransactionCommit(): void
    {
        $callbacks = [];

        $this->db->transBegin();
        $this->db->afterCommit(static function () use (&$callbacks): void {
            $callbacks[] = 'committed';
        });

        $this->assertSame([], $callbacks);

        $this->db->transCommit();

        $this->assertSame(['committed'], $callbacks);
    }

    public function testAfterCommitDoesNotRunAfterTransactionRollsBack(): void
    {
        $callbacks = [];

        $this->db->transStart(true);
        $this->db->afterCommit(static function () use (&$callbacks): void {
            $callbacks[] = 'committed';
        });

        $this->db->transComplete();

        $this->assertSame([], $callbacks);
    }

    public function testAfterCommitRunsAfterOutermostTransactionCommit(): void
    {
        $callbacks = [];

        $this->db->transStart();
        $this->db->afterCommit(static function () use (&$callbacks): void {
            $callbacks[] = 'outer';
        });

        $this->db->transStart();
        $this->db->afterCommit(static function () use (&$callbacks): void {
            $callbacks[] = 'inner';
        });
        $this->db->transComplete();

        $this->assertSame([], $callbacks);

        $this->db->transComplete();

        $this->assertSame(['outer', 'inner'], $callbacks);
    }

    public function testAfterCommitCallbackExceptionBubblesAfterTransactionCommit(): void
    {
        $builder = $this->db->table('job');

        $this->db->transStart();
        $builder->insert([
            'name'        => 'Committed Job',
            'description' => 'The transaction should still commit.',
        ]);
        $this->db->afterCommit(static function (): void {
            throw new RuntimeException('Commit callback failed.');
        });

        try {
            $this->db->transComplete();
            $this->fail('Expected commit callback exception.');
        } catch (RuntimeException $e) {
            $this->assertSame('Commit callback failed.', $e->getMessage());
        }

        $this->seeInDatabase('job', ['name' => 'Committed Job']);
    }

    public function testAfterRollbackDoesNotRunWhenNoTransactionIsActive(): void
    {
        $callbacks = [];

        $result = $this->db->afterRollback(static function () use (&$callbacks): void {
            $callbacks[] = 'rolled back';
        });

        $this->assertSame($this->db, $result);
        $this->assertSame([], $callbacks);
    }

    public function testAfterRollbackRunsAfterTransactionRollsBack(): void
    {
        $callbacks = [];

        $this->db->transStart(true);
        $this->db->afterRollback(static function () use (&$callbacks): void {
            $callbacks[] = 'rolled back';
        });

        $this->assertSame([], $callbacks);

        $this->db->transComplete();

        $this->assertSame(['rolled back'], $callbacks);
    }

    public function testAfterRollbackRunsAfterManualTransactionRollback(): void
    {
        $callbacks = [];

        $this->db->transBegin();
        $this->db->afterRollback(static function () use (&$callbacks): void {
            $callbacks[] = 'rolled back';
        });

        $this->assertSame([], $callbacks);

        $this->db->transRollback();

        $this->assertSame(['rolled back'], $callbacks);
    }

    public function testAfterRollbackDoesNotRunAfterSuccessfulTransactionCommit(): void
    {
        $callbacks = [];

        $this->db->transStart();
        $this->db->afterRollback(static function () use (&$callbacks): void {
            $callbacks[] = 'rolled back';
        });

        $this->db->transComplete();

        $this->assertSame([], $callbacks);
    }

    public function testAfterRollbackRunsAfterOutermostTransactionRollsBack(): void
    {
        $callbacks = [];

        $this->db->transStart();
        $this->db->afterRollback(static function () use (&$callbacks): void {
            $callbacks[] = 'outer';
        });

        $this->db->transStart();
        $this->db->afterRollback(static function () use (&$callbacks): void {
            $callbacks[] = 'inner';
        });
        $this->db->transComplete();

        $this->assertSame([], $callbacks);

        $this->db->transRollback();

        $this->assertSame(['outer', 'inner'], $callbacks);
    }

    public function testAfterRollbackCallbackExceptionBubblesAfterTransactionRollback(): void
    {
        $builder = $this->db->table('job');

        $this->db->transStart(true);
        $builder->insert([
            'name'        => 'Rolled Back Job',
            'description' => 'The transaction should still roll back.',
        ]);
        $this->db->afterRollback(static function (): void {
            throw new RuntimeException('Rollback callback failed.');
        });

        try {
            $this->db->transComplete();
            $this->fail('Expected rollback callback exception.');
        } catch (RuntimeException $e) {
            $this->assertSame('Rollback callback failed.', $e->getMessage());
        }

        $this->dontSeeInDatabase('job', ['name' => 'Rolled Back Job']);
    }

    public function testAfterRollbackCallbackExceptionDoesNotPreventNonStrictStatusReset(): void
    {
        $this->db->transStrict(false);
        $this->db->transStart(true);
        $this->db->afterRollback(static function (): void {
            throw new RuntimeException('Rollback callback failed.');
        });

        try {
            $this->db->transComplete();
            $this->fail('Expected rollback callback exception.');
        } catch (RuntimeException $e) {
            $this->assertSame('Rollback callback failed.', $e->getMessage());
        }

        $this->assertTrue($this->db->transStatus());
    }

    public function testAfterRollbackRunsAfterAutomaticRollbackOnQueryFailure(): void
    {
        $callbacks = [];
        $builder   = $this->db->transException(true)->table('job');

        try {
            $this->db->transStart();
            $this->db->afterRollback(static function () use (&$callbacks): void {
                $callbacks[] = 'rolled back';
            });
            $builder->insert([
                'name'        => 'Rolled Back Job',
                'description' => 'The transaction should roll back.',
            ]);
            $builder->insert([
                'id'          => 1,
                'name'        => 'Duplicate Job',
                'description' => 'This should fail.',
            ]);
        } catch (DatabaseException) {
            // The framework already rolled back while handling the query failure.
        }

        $this->assertSame(['rolled back'], $callbacks);
        $this->dontSeeInDatabase('job', ['name' => 'Rolled Back Job']);
    }
}
