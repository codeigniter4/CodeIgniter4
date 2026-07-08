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

namespace Tests\Support\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Query;
use CodeIgniter\Database\TableName;
use CodeIgniter\Test\Mock\MockConnection;
use stdClass;

/**
 * @internal
 */
final class WithLockedRowConnection extends MockConnection
{
    /**
     * @param list<array<string, mixed>> $rows
     */
    public function __construct(private array $rows = [], public bool $throwOnSelect = false)
    {
        parent::__construct([]);
    }

    /**
     * @param array<int|string, mixed>|string|null $binds
     */
    public function query(string $sql, $binds = null, bool $setEscapeFlags = true, string $queryClass = ''): BaseResult|bool
    {
        if ($this->connID === false || $this->connID === null) {
            $this->initialize();
        }

        $query = new Query($this);
        $query->setQuery($sql, $binds, $setEscapeFlags);

        $this->lastQuery = $query;

        if ($query->isWriteType()) {
            return true;
        }

        if ($this->throwOnSelect) {
            throw new DatabaseException('Locked lookup failed.');
        }

        return new WithLockedRowResult($this->connID, new stdClass(), $this->rows);
    }

    /**
     * @param array<array-key, mixed>|string|TableName $tableName
     */
    public function table($tableName): BaseBuilder
    {
        return new BaseBuilder($tableName, $this);
    }

    protected function execute(string $sql): object
    {
        return new stdClass();
    }
}
