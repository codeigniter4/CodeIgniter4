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

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\RetryableTransactionException;
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
final class TransactionClosureTest extends CIUnitTestCase
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

    /**
     * Sets $DBDebug to false.
     */
    protected function disableDBDebug(): void
    {
        $this->setPrivateProperty($this->db, 'DBDebug', false);
    }

    /**
     * Sets $DBDebug to true.
     */
    protected function enableDBDebug(): void
    {
        $this->setPrivateProperty($this->db, 'DBDebug', true);
    }

    public function testTransactionReturnsCallbackResultAndCommits(): void
    {
        $result = $this->db->transaction(static function (BaseConnection $db): string {
            $db->table('job')->insert([
                'name'        => 'Committed Job',
                'description' => 'The transaction should commit.',
            ]);

            return 'committed';
        });

        $this->assertSame('committed', $result);
        $this->seeInDatabase('job', ['name' => 'Committed Job']);
    }

    public function testTransactionRetriesRetryableExceptionAndCommits(): void
    {
        $attempts  = 0;
        $callbacks = [];

        $result = $this->db->transaction(static function (BaseConnection $db) use (&$attempts, &$callbacks): string {
            $attempts++;

            $db->table('job')->insert([
                'name'        => 'Retried Job ' . $attempts,
                'description' => 'Only the final attempt should commit.',
            ]);
            $db->afterCommit(static function () use (&$callbacks, &$attempts): void {
                $callbacks[] = $attempts;
            });

            if ($attempts === 1) {
                throw new RetryableTransactionException('Deadlock found when trying to get lock.', 1213);
            }

            return 'committed';
        }, attempts: 2);

        $this->assertSame('committed', $result);
        $this->assertSame(2, $attempts);
        $this->assertSame([2], $callbacks);
        $this->dontSeeInDatabase('job', ['name' => 'Retried Job 1']);
        $this->seeInDatabase('job', ['name' => 'Retried Job 2']);
    }

    public function testRollbackCallbacksRunForFailedRetryAttempts(): void
    {
        $attempts  = 0;
        $callbacks = [];

        $result = $this->db->transaction(static function (BaseConnection $db) use (&$attempts, &$callbacks): string {
            $attempts++;

            $db->afterRollback(static function () use (&$callbacks, &$attempts): void {
                $callbacks[] = $attempts;
            });

            if ($attempts === 1) {
                throw new RetryableTransactionException('Deadlock found when trying to get lock.', 1213);
            }

            return 'committed';
        }, attempts: 2);

        $this->assertSame('committed', $result);
        $this->assertSame(2, $attempts);
        $this->assertSame([1], $callbacks);
    }

    public function testRollbackCallbackExceptionStopsRetryAttempts(): void
    {
        $attempts = 0;

        try {
            $this->db->transaction(static function (BaseConnection $db) use (&$attempts): void {
                $attempts++;

                $db->afterRollback(static function (): void {
                    throw new RuntimeException('Rollback callback failed.');
                });

                throw new RetryableTransactionException('Deadlock found when trying to get lock.', 1213);
            }, attempts: 2);
            $this->fail('Expected rollback callback exception.');
        } catch (RuntimeException $e) {
            $this->assertSame('Rollback callback failed.', $e->getMessage());
        }

        $this->assertSame(1, $attempts);
    }

    public function testTransactionRethrowsRetryableExceptionAfterAttemptsAreExhausted(): void
    {
        $attempts = 0;

        try {
            $this->db->transaction(static function (BaseConnection $db) use (&$attempts): void {
                $attempts++;

                $db->table('job')->insert([
                    'name'        => 'Rolled Back Retried Job ' . $attempts,
                    'description' => 'Every attempt should roll back.',
                ]);

                throw new RetryableTransactionException('Deadlock found when trying to get lock.', 1213);
            }, attempts: 2);
            $this->fail('Expected retryable transaction exception.');
        } catch (RetryableTransactionException $e) {
            $this->assertSame('Deadlock found when trying to get lock.', $e->getMessage());
        }

        $this->assertSame(2, $attempts);
        $this->dontSeeInDatabase('job', ['name' => 'Rolled Back Retried Job 1']);
        $this->dontSeeInDatabase('job', ['name' => 'Rolled Back Retried Job 2']);
    }

    public function testTransactionDoesNotRetryNonRetryableException(): void
    {
        $attempts = 0;

        try {
            $this->db->transaction(static function () use (&$attempts): void {
                $attempts++;

                throw new RuntimeException('Transaction callback failed.');
            }, attempts: 3);
            $this->fail('Expected transaction callback exception.');
        } catch (RuntimeException $e) {
            $this->assertSame('Transaction callback failed.', $e->getMessage());
        }

        $this->assertSame(1, $attempts);
    }

    public function testTransactionPassesConnectionToCallback(): void
    {
        $result = $this->db->transaction(fn (BaseConnection $db): bool => $db === $this->db);

        $this->assertTrue($result);
    }

    public function testTransactionRollsBackAndRethrowsCallbackException(): void
    {
        try {
            $this->db->transaction(static function (BaseConnection $db): void {
                $db->table('job')->insert([
                    'name'        => 'Rolled Back Job',
                    'description' => 'The transaction should roll back.',
                ]);

                throw new RuntimeException('Transaction callback failed.');
            });
            $this->fail('Expected transaction callback exception.');
        } catch (RuntimeException $e) {
            $this->assertSame('Transaction callback failed.', $e->getMessage());
        }

        $this->dontSeeInDatabase('job', ['name' => 'Rolled Back Job']);
    }

    public function testTransactionReturnsFalseAndRollsBackWhenTransactionStatusFails(): void
    {
        $this->disableDBDebug();

        $result = $this->db->transaction(static function (BaseConnection $db): string {
            $builder = $db->table('job');

            $builder->insert([
                'name'        => 'Rolled Back Job',
                'description' => 'The transaction should roll back.',
            ]);
            $builder->insert([
                'id'          => 1,
                'name'        => 'Duplicate Job',
                'description' => 'This should fail.',
            ]);

            return 'not returned';
        });

        $this->assertFalse($result);
        $this->dontSeeInDatabase('job', ['name' => 'Rolled Back Job']);

        $this->enableDBDebug();
    }

    public function testTransactionCallbackExceptionDoesNotPreventNonStrictStatusReset(): void
    {
        $this->disableDBDebug();
        $this->db->transStrict(false);

        try {
            $this->db->transaction(static function (BaseConnection $db): void {
                $db->table('job')->insert([
                    'id'          => 1,
                    'name'        => 'Duplicate Job',
                    'description' => 'This should fail.',
                ]);

                throw new RuntimeException('Transaction callback failed.');
            });
            $this->fail('Expected transaction callback exception.');
        } catch (RuntimeException $e) {
            $this->assertSame('Transaction callback failed.', $e->getMessage());
        }

        $this->assertTrue($this->db->transStatus());

        $this->enableDBDebug();
    }

    public function testTransactionRunsAfterCommitCallbacksAfterSuccessfulCommit(): void
    {
        $callbacks = [];

        $result = $this->db->transaction(static function (BaseConnection $db) use (&$callbacks): string {
            $db->afterCommit(static function () use (&$callbacks): void {
                $callbacks[] = 'committed';
            });

            $db->table('job')->insert([
                'name'        => 'Committed Job',
                'description' => 'The transaction should commit.',
            ]);

            return 'committed';
        });

        $this->assertSame('committed', $result);
        $this->assertSame(['committed'], $callbacks);
    }

    public function testTransactionRunsAfterRollbackCallbacksAfterCallbackException(): void
    {
        $callbacks = [];

        try {
            $this->db->transaction(static function (BaseConnection $db) use (&$callbacks): void {
                $db->afterRollback(static function () use (&$callbacks): void {
                    $callbacks[] = 'rolled back';
                });

                throw new RuntimeException('Transaction callback failed.');
            });
            $this->fail('Expected transaction callback exception.');
        } catch (RuntimeException) {
            // The rollback callback should have already run.
        }

        $this->assertSame(['rolled back'], $callbacks);
    }

    public function testRollbackCallbackExceptionBubblesWhenCallbackExceptionTriggersRollback(): void
    {
        try {
            $this->db->transaction(static function (BaseConnection $db): void {
                $db->afterRollback(static function (): void {
                    throw new RuntimeException('Rollback callback failed.');
                });

                $db->table('job')->insert([
                    'name'        => 'Rolled Back Job',
                    'description' => 'The transaction should roll back.',
                ]);

                throw new RuntimeException('Transaction callback failed.');
            });
            $this->fail('Expected rollback callback exception.');
        } catch (RuntimeException $e) {
            $this->assertSame('Rollback callback failed.', $e->getMessage());
        }

        $this->assertLogContains('error', 'Transaction callback failed.');
        $this->dontSeeInDatabase('job', ['name' => 'Rolled Back Job']);
    }

    public function testNestedTransactionCallbacksRunAfterOutermostCommit(): void
    {
        $callbacks = [];

        $this->db->transStart();

        $result = $this->db->transaction(static function (BaseConnection $db) use (&$callbacks): string {
            $db->afterCommit(static function () use (&$callbacks): void {
                $callbacks[] = 'committed';
            });

            return 'nested';
        });

        $this->assertSame('nested', $result);
        $this->assertSame([], $callbacks);

        $this->db->transComplete();

        $this->assertSame(['committed'], $callbacks);
    }

    public function testNestedTransactionWritesCommitAfterOutermostCommit(): void
    {
        $this->db->transStart();

        $this->db->table('job')->insert([
            'name'        => 'Outer Job',
            'description' => 'The outer transaction should commit.',
        ]);

        $result = $this->db->transaction(static function (BaseConnection $db): string {
            $db->table('job')->insert([
                'name'        => 'Inner Job',
                'description' => 'The nested transaction should commit.',
            ]);

            return 'nested';
        });

        $this->assertSame('nested', $result);

        $this->db->transComplete();

        $this->seeInDatabase('job', ['name' => 'Outer Job']);
        $this->seeInDatabase('job', ['name' => 'Inner Job']);
    }

    public function testNestedTransactionCallbackExceptionMarksOuterTransactionForRollback(): void
    {
        $this->db->transStart();

        $this->db->table('job')->insert([
            'name'        => 'Outer Job',
            'description' => 'The outer transaction should roll back.',
        ]);

        try {
            $this->db->transaction(static function (BaseConnection $db): void {
                $db->table('job')->insert([
                    'name'        => 'Inner Job',
                    'description' => 'The nested transaction should roll back.',
                ]);

                throw new RuntimeException('Nested transaction callback failed.');
            });
            $this->fail('Expected nested transaction callback exception.');
        } catch (RuntimeException $e) {
            $this->assertSame('Nested transaction callback failed.', $e->getMessage());
        }

        $this->assertFalse($this->db->transStatus());

        $this->db->transComplete();

        $this->dontSeeInDatabase('job', ['name' => 'Outer Job']);
        $this->dontSeeInDatabase('job', ['name' => 'Inner Job']);
    }

    public function testNestedTransactionRetryAttemptsRunOnce(): void
    {
        $attempts = 0;

        $this->db->transStart();

        try {
            $this->db->transaction(static function () use (&$attempts): void {
                $attempts++;

                throw new RetryableTransactionException('Deadlock found when trying to get lock.', 1213);
            }, attempts: 3);
            $this->fail('Expected retryable transaction exception.');
        } catch (RetryableTransactionException) {
            // Nested transactions cannot be safely replayed without savepoints.
        }

        $this->assertSame(1, $attempts);
        $this->assertFalse($this->db->transStatus());

        $this->db->transComplete();
    }

    public function testAfterCommitCallbackExceptionBubblesAfterTransactionCommit(): void
    {
        try {
            $this->db->transaction(static function (BaseConnection $db): void {
                $db->table('job')->insert([
                    'name'        => 'Committed Job',
                    'description' => 'The transaction should still commit.',
                ]);
                $db->afterCommit(static function (): void {
                    throw new RuntimeException('Commit callback failed.');
                });
            });
            $this->fail('Expected commit callback exception.');
        } catch (RuntimeException $e) {
            $this->assertSame('Commit callback failed.', $e->getMessage());
        }

        $this->seeInDatabase('job', ['name' => 'Committed Job']);
    }
}
