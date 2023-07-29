<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Live;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class TransactionDBDebugFalseTest extends TransactionDBDebugTrueTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->disableDBDebug();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->enableDBDebug();
    }

    public function testTransStartTransException(): void
    {
        $builder = $this->db->table('job');

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

        $this->dontSeeInDatabase('job', ['name' => 'Grocery Sales']);
    }
}
