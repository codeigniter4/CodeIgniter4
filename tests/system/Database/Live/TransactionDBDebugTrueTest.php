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
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @internal
 *
 * @no-final
 */
#[Group('DatabaseLive')]
class TransactionDBDebugTrueTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    protected function setUp(): void
    {
        // Reset connection instance.
        $this->db = Database::connect($this->DBGroup, false);
        $this->assertFalse($this->db->enableSavepoints);

        parent::setUp();
    }

    /**
     * Sets $DBDebug to false.
     *
     * WARNING: this value will persist! take care to roll it back.
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

    public function testTransStart(): void
    {
        $builder = $this->db->table('job');

        $this->db->transStart();

        $jobData = [
            'name'        => 'Grocery Sales',
            'description' => 'Discount!',
        ];
        $builder->insert($jobData);

        // Duplicate entry '1' for key 'PRIMARY'
        $jobData = [
            'id'          => 1,
            'name'        => 'Comedian',
            'description' => 'Theres something in your teeth',
        ];
        $builder->insert($jobData);

        $this->db->transComplete();

        $this->assertFalse($this->db->transStatus());
        $this->dontSeeInDatabase('job', ['name' => 'Grocery Sales']);
    }

    public function testTransStartTransException(): void
    {
        $builder = $this->db->table('job');
        $e       = null;

        try {
            $this->db->transException(true)->transStart();

            $jobData = [
                'name'        => 'Grocery Sales',
                'description' => 'Discount!',
            ];
            $builder->insert($jobData);

            // Duplicate entry '1' for key 'PRIMARY'
            $jobData = [
                'id'          => 1,
                'name'        => 'Comedian',
                'description' => 'Theres something in your teeth',
            ];
            $builder->insert($jobData);

            $this->db->transComplete();
        } catch (DatabaseException $e) {
            // Do nothing.
        }

        $this->assertInstanceOf(DatabaseException::class, $e);
        $this->dontSeeInDatabase('job', ['name' => 'Grocery Sales']);
    }

    public function testTransStrictTrue(): void
    {
        $builder = $this->db->table('job');

        // The first transaction group
        $this->db->transStart();

        $jobData = [
            'name'        => 'Grocery Sales',
            'description' => 'Discount!',
        ];
        $builder->insert($jobData);

        $this->assertTrue($this->db->transStatus());

        // Duplicate entry '1' for key 'PRIMARY'
        $jobData = [
            'id'          => 1,
            'name'        => 'Comedian',
            'description' => 'Theres something in your teeth',
        ];
        $builder->insert($jobData);

        $this->assertFalse($this->db->transStatus());

        $this->db->transComplete();

        $this->dontSeeInDatabase('job', ['name' => 'Grocery Sales']);

        // The second transaction group
        $this->db->transStart();

        $jobData = [
            'name'        => 'Comedian',
            'description' => 'Theres something in your teeth',
        ];
        $builder->insert($jobData);

        $this->assertFalse($this->db->transStatus());

        $this->db->transComplete();

        $this->dontSeeInDatabase('job', ['name' => 'Comedian']);
    }

    public function testTransStrictFalse(): void
    {
        $builder = $this->db->table('job');

        $this->db->transStrict(false);

        // The first transaction group
        $this->db->transStart();

        $jobData = [
            'name'        => 'Grocery Sales',
            'description' => 'Discount!',
        ];
        $builder->insert($jobData);

        $this->assertTrue($this->db->transStatus());

        // Duplicate entry '1' for key 'PRIMARY'
        $jobData = [
            'id'          => 1,
            'name'        => 'Comedian',
            'description' => 'Theres something in your teeth',
        ];
        $builder->insert($jobData);

        $this->assertFalse($this->db->transStatus());

        $this->db->transComplete();

        $this->dontSeeInDatabase('job', ['name' => 'Grocery Sales']);

        // The second transaction group
        $this->db->transStart();

        $jobData = [
            'name'        => 'Comedian',
            'description' => 'Theres something in your teeth',
        ];
        $builder->insert($jobData);

        $this->assertTrue($this->db->transStatus());

        $this->db->transComplete();

        $this->seeInDatabase('job', ['name' => 'Comedian']);
    }

    public function testTransBegin(): void
    {
        $builder = $this->db->table('job');

        $this->db->transBegin();

        $jobData = [
            'name'        => 'Grocery Sales',
            'description' => 'Discount!',
        ];
        $builder->insert($jobData);

        // Duplicate entry '1' for key 'PRIMARY'
        $jobData = [
            'id'          => 1,
            'name'        => 'Comedian',
            'description' => 'Theres something in your teeth',
        ];
        $builder->insert($jobData);

        $this->assertFalse($this->db->transStatus());

        $this->db->transRollback();

        $this->dontSeeInDatabase('job', ['name' => 'Grocery Sales']);
    }

    /**
     * @depends testTransStart
     */
    public function testNestedTransactionsWhenDisabled(): void
    {
        $builder = $this->db->table('job');

        $this->db->transStart() || $this->fail('Failed to start transaction');

        $jobData = [
            'name'        => 'Grocery Sales',
            'description' => 'Fresh!',
        ];
        $builder->insert($jobData);

        $this->db->transStart() || $this->fail('Failed to start inner transaction');

        $jobData = [
            'name'        => 'Comedian',
            'description' => 'Theres something in your teeth',
        ];
        $builder->insert($jobData);

        $this->db->transComplete() || $this->fail('Failed to complete inner transaction');

        $this->db->transComplete() || $this->fail('Failed to complete outer transaction');

        $this->seeInDatabase('job', ['name' => 'Grocery Sales']);
        $this->seeInDatabase('job', ['name' => 'Comedian']);
    }

    /**
     * @depends testTransStart
     */
    public function testNestedTransactionsWhenDisabledRollbackOuter(): void
    {
        $builder = $this->db->table('job');

        $this->db->transStart() || $this->fail('Failed to start transaction');

        $jobData = [
            'name'        => 'Grocery Sales',
            'description' => 'Fresh!',
        ];
        $builder->insert($jobData);

        $this->db->transStart() || $this->fail('Failed to start inner transaction');

        $jobData = [
            'name'        => 'Comedian',
            'description' => 'Theres something in your teeth',
        ];
        $builder->insert($jobData);

        $this->db->transComplete() || $this->fail('Failed to complete inner transaction');

        $this->db->transRollback() || $this->fail('Failed to rollback outer transaction');

        $this->dontSeeInDatabase('job', ['name' => 'Grocery Sales']);
        $this->dontSeeInDatabase('job', ['name' => 'Comedian']);
    }

    /**
     * @depends testTransStart
     */
    public function testNestedTransactionsWhenDisabledRollbackOnInnerHasNoEffect()
    {
        $this->db->transStrict(false); // TODO: this only works when strict is disabled
        $builder = $this->db->table('job');

        $this->db->transStart() || $this->fail('Failed to start transaction');

        $jobData = [
            'name'        => 'Grocery Sales',
            'description' => 'Fresh!',
        ];
        $builder->insert($jobData);

        $this->db->transStart() || $this->fail('Failed to start inner transaction');

        $jobData = [
            'name'        => 'Comedian',
            'description' => 'Theres something in your teeth',
        ];

        $this->db->query('invalid query to throw a Database Exception');
        $builder->insert($jobData);

        $this->db->transRollback() || $this->fail('Failed to rollback inner transaction');

        $this->db->transComplete() || $this->fail('Failed to complete outer transaction');

        $this->seeInDatabase('job', ['name' => 'Grocery Sales']);
        $this->seeInDatabase('job', ['name' => 'Comedian']);
    }

    /**
     * @depends testTransStart
     */
    public function testNestedTransactionsWhenDisabledAlwaysRollsBackEverythingOnDatabaseException(): void
    {
        $builder = $this->db->table('job');

        $this->db->transStart() || $this->fail('Failed to start transaction');

        $jobData = [
            'name'        => 'Grocery Sales',
            'description' => 'Fresh!',
        ];
        $builder->insert($jobData);

        $this->db->transStart() || $this->fail('Failed to start inner transaction');

        $jobData = [
            'name'        => 'Comedian',
            'description' => 'Theres something in your teeth',
        ];

        $this->db->query('invalid query to throw a Database Exception');
        $builder->insert($jobData);

        $this->db->transComplete() && $this->fail('Should not have completed inner transaction, but rolled it back');

        $this->db->transComplete() && $this->fail('Should not have completed outer transaction, but rolled it back');

        $this->dontSeeInDatabase('job', ['name' => 'Grocery Sales']);
        $this->dontSeeInDatabase('job', ['name' => 'Comedian']);
    }

    /**
     * @depends testNestedTransactionsWhenDisabled
     */
    public function testNestedTransactionsWhenEnabled()
    {
        $this->db->transSavepoints(true);
        if (! $this->db->enableSavepoints) {
            $this->markTestSkipped('Nested transactions are not supported for this driver.');
        }

        $this->testNestedTransactionsWhenDisabled();
    }

    /**
     * @depends testNestedTransactionsWhenDisabledRollbackOuter
     * @depends testNestedTransactionsWhenEnabled
     */
    public function testNestedTransactionsWhenEnabledRollbackOuter(): void
    {
        $this->db->transSavepoints(true);
        if (! $this->db->enableSavepoints) {
            $this->markTestSkipped('Nested transactions are not supported for this driver.');
        }

        $this->testNestedTransactionsWhenDisabledRollbackOuter();
    }

    /**
     * @depends testNestedTransactionsWhenEnabled
     */
    public function testNestedTransactionsRollbackInner(): void
    {
        $this->db->transSavepoints(true);
        $this->db->transStrict(false); // TODO: this only works when strict is disabled
        if (! $this->db->enableSavepoints) {
            $this->markTestSkipped('Nested transactions are not supported for this driver.');
        }

        $builder = $this->db->table('job');

        $this->db->transStart() || $this->fail('Failed to start transaction');

        $jobData = [
            'name'        => 'Grocery Sales',
            'description' => 'Fresh!',
        ];
        $builder->insert($jobData);

        $this->db->transStart() || $this->fail('Failed to start inner transaction');

        $jobData = [
            'name'        => 'Comedian',
            'description' => 'Theres something in your teeth',
        ];
        $builder->insert($jobData);

        $this->db->transRollback() || $this->fail('Failed to rollback inner transaction');

        $this->db->transComplete() || $this->fail('Failed to complete outer transaction');

        $this->seeInDatabase('job', ['name' => 'Grocery Sales']);
        $this->dontSeeInDatabase('job', ['name' => 'Comedian']);
    }

    /**
     * @depends testNestedTransactionsWhenEnabled
     */
    public function testHeavilyNestedTransactionsWhenEnabled()
    {
        $this->db->transSavepoints(true);
        $this->db->transStrict(false); // TODO: this only works when strict is disabled
        if (! $this->db->enableSavepoints) {
            $this->markTestSkipped('Nested transactions are not supported for this driver.');
        }

        $sees     = [];
        $dontSees = [];
        $db       = $this->db;
        $insert   = static function (string $data, bool $expectCommitted = true) use ($db, &$sees, &$dontSees) {
            $builder = $db->table('job');
            $jobData = [
                'name'        => $data,
                'description' => 'this should have been rolled back',
            ];
            $builder->insert($jobData);

            if ($expectCommitted) {
                $sees[] = $data;
            } else {
                $dontSees[] = $data;
            }
        };

        $transaction = static function (string $data_to_insert, bool $complete = true, bool $expect_committed = true, ?callable $inner = null) use ($db, $insert) {
            $db->transStart();
            $insert($data_to_insert . '_before');
            is_null($inner) || $inner();
            $insert($data_to_insert . '_after');
            if ($complete) {
                $db->transComplete();
            } else {
                $db->transRollback();
            }
        };

        $nest_transaction_times = static function (int $times, ...$params) use ($transaction) {
            $extend = $params[0];

            for ($i = 0; $i < $times; $i++) {
                $params[0] .= '_' . $extend;
                $params[3] = $transaction(...$params);
            }
        };

        $transaction('outer', true, true, static function () use ($transaction, $nest_transaction_times) {
            $transaction('inner_1', false, false, static function () use ($transaction) {
                $transaction('inner_1_1', true, false);
                $transaction('inner_1_2', false, false);
                $transaction('inner_1_3', true, false, static function () use ($transaction) {
                    $transaction('inner_1_3_1', true, false);
                    $transaction('inner_1_3_2', false, false);
                });
                $transaction('inner_1_5', true, false);
            });
            $transaction('inner_2', true, true, static function () use ($transaction) {
                $transaction('inner_2_1', true, true);
                $transaction('inner_2_2', true, true, static function () use ($transaction) {
                    $transaction('inner_2_2_1', true, true);
                    $transaction('inner_2_2_2', false, false);
                    $transaction('inner_2_2_2', true, true, static function () use ($transaction) {
                        $transaction('inner_2_2_2_1', true, true);
                        $transaction('inner_2_2_2_2', false, false);
                    });
                });

                $transaction('inner_2_3', false, false);

                $transaction('inner_2_4', false, false, static function () use ($transaction) {
                    $transaction('inner_2_4_1', true, false);
                    $transaction('inner_2_4_2', false, false);
                });

                $transaction('inner_2_5', true, false);
            });

            $nest_transaction_times(10, 'inner_3', true, true, static function () use ($transaction) {
                $transaction('inner_3_x10_1', true, true);
                $transaction('inner_3_x10_2', false, false);
            });

            $nest_transaction_times(10, 'inner_4', false, false, static function () use ($transaction) {
                $transaction('inner_4_x10_1', true, false);
                $transaction('inner_4_x10_2', false, false);
            });
        });

        $this->assertNotEmpty($sees);
        $this->assertNotEmpty($dontSees);

        foreach ($sees as $see) {
            $this->seeInDatabase('job', $see);
        }

        foreach ($dontSees as $see) {
            $this->dontSeeInDatabase('job', $see);
        }
    }
}
