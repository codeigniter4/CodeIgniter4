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
 * @see \CodeIgniter\Database\TableNameTest
 */
class TableName
{
    /**
     * @param string $dbPrefix         DB prefix
     * @param string $table            Table name (w/o DB prefix)
     * @param string $escapedTableName Escaped table name (w/z DB prefix)
     * @param string $escapedAliasName Escaped alias name
     * @param string $schema           Schema name
     * @param string $database         Database name
     * @param string $alias            Alias name
     */
    protected function __construct(
        private string $dbPrefix,
        private string $table,
        private string $escapedTableName,
        private string $escapedAliasName,
        private string $schema = '',
        private string $database = '',
        private string $alias = ''
    ) {
    }

    /**
     * Creates a new instance.
     *
     * @param string $table Table name (w/o DB prefix)
     * @param string $alias Alias name
     */
    public static function create(BaseConnection $db, string $table, string $alias = ''): self
    {
        $escapedTableName = $db->escapeIdentifier($db->DBPrefix . $table);
        $escapedAliasName = $db->escapeIdentifier($alias);

        return new self(
            $db->DBPrefix,
            $table,
            $escapedTableName,
            $escapedAliasName,
            '',
            '',
            $alias
        );
    }

    /**
     * Creates a new instance from an actual table name.
     *
     * @param string $table Actual table name with DB prefix
     * @param string $alias Alias name
     */
    public static function fromActualName(BaseConnection $db, string $table, string $alias = ''): self
    {
        $escapedTableName = $db->escapeIdentifier($table);
        $escapedAliasName = $db->escapeIdentifier($alias);

        return new self(
            '',
            $table,
            $escapedTableName,
            $escapedAliasName,
            '',
            '',
            $alias
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
        BaseConnection $db,
        string $table,
        string $schema = '',
        string $database = '',
        string $alias = ''
    ): self {
        $escapedTableName = '';
        if ($database !== '') {
            $escapedTableName .= $db->escapeIdentifier($database) . '.';
        }
        if ($schema !== '') {
            $escapedTableName .= $db->escapeIdentifier($schema) . '.';
        }
        $escapedTableName .= $db->escapeIdentifier($db->DBPrefix . $table);

        $escapedAliasName = $db->escapeIdentifier($alias);

        return new self(
            $db->DBPrefix,
            $table,
            $escapedTableName,
            $escapedAliasName,
            $schema,
            $database,
            $alias
        );
    }

    /**
     * Returns the single segment table name w/o DB prefix.
     */
    public function getTableName(): string
    {
        return $this->table;
    }

    /**
     * Returns the actual single segment table name w/z DB prefix.
     */
    public function getActualTableName(): string
    {
        return $this->dbPrefix . $this->table;
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

    /**
     * Returns the escaped table name.
     */
    public function getEscapedTableName(): string
    {
        return $this->escapedTableName;
    }

    /**
     * Returns the escaped table name with alias.
     */
    public function getEscapedTableNameWithAlias(): string
    {
        if ($this->escapedAliasName === '') {
            return $this->escapedTableName;
        }

        return $this->escapedTableName . ' ' . $this->escapedAliasName;
    }
}
