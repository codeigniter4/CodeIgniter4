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

namespace CodeIgniter\Models;

use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Models\UserModel;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class ExplainModelTest extends LiveModelTestCase
{
    public function testExplainRespectsSoftDeletesInTestMode(): void
    {
        if (in_array($this->db->DBDriver, ['OCI8', 'SQLSRV'], true)) {
            $this->markTestSkipped($this->db->DBDriver . ' does not support explain().');
        }

        $this->createModel(UserModel::class);

        $sql = $this->model->where('id', 1)->explain(test: true);

        $expectedPrefix = $this->db->DBDriver === 'SQLite3'
            ? 'EXPLAIN QUERY PLAN SELECT'
            : 'EXPLAIN SELECT';

        $this->assertStringStartsWith($expectedPrefix, str_replace("\n", ' ', (string) $sql));
        $this->assertStringContainsString('deleted_at', (string) $sql);
    }

    public function testExplainWithDeletedOmitsSoftDeleteConstraintInTestMode(): void
    {
        if (in_array($this->db->DBDriver, ['OCI8', 'SQLSRV'], true)) {
            $this->markTestSkipped($this->db->DBDriver . ' does not support explain().');
        }

        $this->createModel(UserModel::class);

        $sql = $this->model->withDeleted()->where('id', 1)->explain(test: true);

        $this->assertStringNotContainsString('deleted_at', (string) $sql);
    }
}
