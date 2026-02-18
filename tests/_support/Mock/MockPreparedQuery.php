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

namespace Tests\Support\Mock;

use CodeIgniter\Database\BasePreparedQuery;

/**
 * @internal
 *
 * @extends BasePreparedQuery<object, object, object>
 */
final class MockPreparedQuery extends BasePreparedQuery
{
    public string $preparedSql = '';

    /**
     * @param array<string, mixed> $options
     */
    public function _prepare(string $sql, array $options = []): self
    {
        $this->preparedSql = $sql;

        return $this;
    }

    /**
     * @param array<int, mixed> $data
     */
    public function _execute(array $data): bool
    {
        return true;
    }

    public function _getResult()
    {
        return null;
    }

    protected function _close(): bool
    {
        return true;
    }
}
