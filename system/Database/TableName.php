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

namespace CodeIgniter\Database;

/**
 * Represents a table name in SQL.
 *
 * @interal
 *
 * @see \CodeIgniter\Database\TableNameTest
 */
class TableName
{
    /**
     * @param string $actualTable  Actual table name
     * @param string $logicalTable Logical table name (w/o DB prefix)
     * @param string $schema       Schema name
     * @param string $database     Database name
     * @param string $alias        Alias name
     */
    protected function __construct(
        private readonly string $actualTable,
        private readonly string $logicalTable = '',
        private readonly string $schema = '',
        private readonly string $database = '',
        private readonly string $alias = '',
    ) {
    }

    /**
     * Creates a new instance.
     *
     * @param string $table Table name (w/o DB prefix)
     * @param string $alias Alias name
     */
    public static function create(string $dbPrefix, string $table, string $alias = ''): self
    {
        return new self(
            $dbPrefix . $table,
            $table,
            '',
            '',
            $alias,
        );
    }

    /**
     * Creates a new instance from an actual table name.
     *
     * @param string $actualTable Actual table name with DB prefix
     * @param string $alias       Alias name
     */
    public static function fromActualName(string $dbPrefix, string $actualTable, string $alias = ''): self
    {
        $prefix       = $dbPrefix;
        $logicalTable = '';

        if (str_starts_with($actualTable, $prefix)) {
            $logicalTable = substr($actualTable, strlen($prefix));
        }

        return new self(
            $actualTable,
            $logicalTable,
            '',
            $alias,
        );
    }

    /**
     * Creates a new instance from full name.
     *
     * @param string $table    Table name (w/o DB prefix)
     * @param string $schema   Schema name
     * @param string $database Database name
     * @param string $alias    Alias name
     */
    public static function fromFullName(
        string $dbPrefix,
        string $table,
        string $schema = '',
        string $database = '',
        string $alias = '',
    ): self {
        return new self(
            $dbPrefix . $table,
            $table,
            $schema,
            $database,
            $alias,
        );
    }

    /**
     * Returns the single segment table name w/o DB prefix.
     */
    public function getTableName(): string
    {
        return $this->logicalTable;
    }

    /**
     * Returns the actual single segment table name w/z DB prefix.
     */
    public function getActualTableName(): string
    {
        return $this->actualTable;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getSchema(): string
    {
        return $this->schema;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }
}
