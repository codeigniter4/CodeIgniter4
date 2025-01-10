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

namespace CodeIgniter\Database\OCI8;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\RawSql;

/**
 * Builder for OCI8
 */
class Builder extends BaseBuilder
{
    /**
     * Identifier escape character
     *
     * @var string
     */
    protected $escapeChar = '"';

    /**
     * ORDER BY random keyword
     *
     * @var array
     */
    protected $randomKeyword = [
        '"DBMS_RANDOM"."RANDOM"',
    ];

    /**
     * COUNT string
     *
     * @used-by CI_DB_driver::count_all()
     * @used-by BaseBuilder::count_all_results()
     *
     * @var string
     */
    protected $countString = 'SELECT COUNT(1) ';

    /**
     * Limit used flag
     *
     * If we use LIMIT, we'll add a field that will
     * throw off num_fields later.
     *
     * @var bool
     */
    protected $limitUsed = false;

    /**
     * A reference to the database connection.
     *
     * @var Connection
     */
    protected $db;

    /**
     * Generates a platform-specific insert string from the supplied data.
     */
    protected function _insertBatch(string $table, array $keys, array $values): string
    {
        $sql = $this->QBOptions['sql'] ?? '';

        // if this is the first iteration of batch then we need to build skeleton sql
        if ($sql === '') {
            $insertKeys    = implode(', ', $keys);
            $hasPrimaryKey = in_array('PRIMARY', array_column($this->db->getIndexData($table), 'type'), true);

            // ORA-00001 measures
            $sql = 'INSERT' . ($hasPrimaryKey ? '' : ' ALL') . ' INTO ' . $table . ' (' . $insertKeys . ")\n{:_table_:}";

            $this->QBOptions['sql'] = $sql;
        }

        if (isset($this->QBOptions['setQueryAsData'])) {
            $data = $this->QBOptions['setQueryAsData'];
        } else {
            $data = implode(
                " FROM DUAL UNION ALL\n",
                array_map(
                    static fn ($value): string => 'SELECT ' . implode(', ', array_map(
                        static fn ($key, $index): string => $index . ' ' . $key,
                        $keys,
                        $value,
                    )),
                    $values,
                ),
            ) . " FROM DUAL\n";
        }

        return str_replace('{:_table_:}', $data, $sql);
    }

    /**
     * Generates a platform-specific replace string from the supplied data
     */
    protected function _replace(string $table, array $keys, array $values): string
    {
        $fieldNames = array_map(static fn ($columnName): string => trim($columnName, '"'), $keys);

        $uniqueIndexes = array_filter($this->db->getIndexData($table), static function ($index) use ($fieldNames): bool {
            $hasAllFields = count(array_intersect($index->fields, $fieldNames)) === count($index->fields);

            return ($index->type === 'PRIMARY') && $hasAllFields;
        });
        $replaceableFields = array_filter($keys, static function ($columnName) use ($uniqueIndexes): bool {
            foreach ($uniqueIndexes as $index) {
                if (in_array(trim($columnName, '"'), $index->fields, true)) {
                    return false;
                }
            }

            return true;
        });

        $sql = 'MERGE INTO ' . $table . "\n USING (SELECT ";

        $sql .= implode(', ', array_map(static fn ($columnName, $value): string => $value . ' ' . $columnName, $keys, $values));

        $sql .= ' FROM DUAL) "_replace" ON ( ';

        $onList   = [];
        $onList[] = '1 != 1';

        foreach ($uniqueIndexes as $index) {
            $onList[] = '(' . implode(' AND ', array_map(static fn ($columnName): string => $table . '."' . $columnName . '" = "_replace"."' . $columnName . '"', $index->fields)) . ')';
        }

        $sql .= implode(' OR ', $onList) . ') WHEN MATCHED THEN UPDATE SET ';

        $sql .= implode(', ', array_map(static fn ($columnName): string => $columnName . ' = "_replace".' . $columnName, $replaceableFields));

        $sql .= ' WHEN NOT MATCHED THEN INSERT (' . implode(', ', $replaceableFields) . ') VALUES ';

        return $sql . (' (' . implode(', ', array_map(static fn ($columnName): string => '"_replace".' . $columnName, $replaceableFields)) . ')');
    }

    /**
     * Generates a platform-specific truncate string from the supplied data
     *
     * If the database does not support the truncate() command,
     * then this method maps to 'DELETE FROM table'
     */
    protected function _truncate(string $table): string
    {
        return 'TRUNCATE TABLE ' . $table;
    }

    /**
     * Compiles a delete string and runs the query
     *
     * @param mixed $where
     *
     * @return mixed
     *
     * @throws DatabaseException
     */
    public function delete($where = '', ?int $limit = null, bool $resetData = true)
    {
        if ($limit !== null && $limit !== 0) {
            $this->QBLimit = $limit;
        }

        return parent::delete($where, null, $resetData);
    }

    /**
     * Generates a platform-specific delete string from the supplied data
     */
    protected function _delete(string $table): string
    {
        if ($this->QBLimit) {
            $this->where('rownum <= ', $this->QBLimit, false);
            $this->QBLimit = false;
        }

        return parent::_delete($table);
    }

    /**
     * Generates a platform-specific update string from the supplied data
     */
    protected function _update(string $table, array $values): string
    {
        $valStr = [];

        foreach ($values as $key => $val) {
            $valStr[] = $key . ' = ' . $val;
        }

        if ($this->QBLimit) {
            $this->where('rownum <= ', $this->QBLimit, false);
        }

        return 'UPDATE ' . $this->compileIgnore('update') . $table . ' SET ' . implode(', ', $valStr)
            . $this->compileWhereHaving('QBWhere')
            . $this->compileOrderBy();
    }

    /**
     * Generates a platform-specific LIMIT clause.
     */
    protected function _limit(string $sql, bool $offsetIgnore = false): string
    {
        $offset = (int) ($offsetIgnore === false ? $this->QBOffset : 0);
        if (version_compare($this->db->getVersion(), '12.1', '>=')) {
            // OFFSET-FETCH can be used only with the ORDER BY clause
            if (empty($this->QBOrderBy)) {
                $sql .= ' ORDER BY 1';
            }

            return $sql . ' OFFSET ' . $offset . ' ROWS FETCH NEXT ' . $this->QBLimit . ' ROWS ONLY';
        }

        $this->limitUsed    = true;
        $limitTemplateQuery = 'SELECT * FROM (SELECT INNER_QUERY.*, ROWNUM RNUM FROM (%s) INNER_QUERY WHERE ROWNUM < %d)' . ($offset !== 0 ? ' WHERE RNUM >= %d' : '');

        return sprintf($limitTemplateQuery, $sql, $offset + $this->QBLimit + 1, $offset);
    }

    /**
     * Resets the query builder values.  Called by the get() function
     */
    protected function resetSelect()
    {
        $this->limitUsed = false;
        parent::resetSelect();
    }

    /**
     * Generates a platform-specific batch update string from the supplied data
     */
    protected function _updateBatch(string $table, array $keys, array $values): string
    {
        $sql = $this->QBOptions['sql'] ?? '';

        // if this is the first iteration of batch then we need to build skeleton sql
        if ($sql === '') {
            $constraints = $this->QBOptions['constraints'] ?? [];

            if ($constraints === []) {
                if ($this->db->DBDebug) {
                    throw new DatabaseException('You must specify a constraint to match on for batch updates.');
                }

                return ''; // @codeCoverageIgnore
            }

            $updateFields = $this->QBOptions['updateFields'] ??
                $this->updateFields($keys, false, $constraints)->QBOptions['updateFields'] ??
                [];

            $alias = $this->QBOptions['alias'] ?? '"_u"';

            // Oracle doesn't support ignore on updates so we will use MERGE
            $sql = 'MERGE INTO ' . $table . "\n";

            $sql .= "USING (\n{:_table_:}";

            $sql .= ') ' . $alias . "\n";

            $sql .= 'ON (' . implode(
                ' AND ',
                array_map(
                    static fn ($key, $value) => (
                        ($value instanceof RawSql && is_string($key))
                        ?
                        $table . '.' . $key . ' = ' . $value
                        :
                        (
                            $value instanceof RawSql
                            ?
                            $value
                            :
                            $table . '.' . $value . ' = ' . $alias . '.' . $value
                        )
                    ),
                    array_keys($constraints),
                    $constraints,
                ),
            ) . ")\n";

            $sql .= "WHEN MATCHED THEN UPDATE\n";

            $sql .= "SET\n";

            $sql .= implode(
                ",\n",
                array_map(
                    static fn ($key, $value): string => $table . '.' . $key . ($value instanceof RawSql ?
                    ' = ' . $value :
                    ' = ' . $alias . '.' . $value),
                    array_keys($updateFields),
                    $updateFields,
                ),
            );

            $this->QBOptions['sql'] = $sql;
        }

        if (isset($this->QBOptions['setQueryAsData'])) {
            $data = $this->QBOptions['setQueryAsData'];
        } else {
            $data = implode(
                " UNION ALL\n",
                array_map(
                    static fn ($value): string => 'SELECT ' . implode(', ', array_map(
                        static fn ($key, $index): string => $index . ' ' . $key,
                        $keys,
                        $value,
                    )) . ' FROM DUAL',
                    $values,
                ),
            ) . "\n";
        }

        return str_replace('{:_table_:}', $data, $sql);
    }

    /**
     * Generates a platform-specific upsertBatch string from the supplied data
     *
     * @throws DatabaseException
     */
    protected function _upsertBatch(string $table, array $keys, array $values): string
    {
        $sql = $this->QBOptions['sql'] ?? '';

        // if this is the first iteration of batch then we need to build skeleton sql
        if ($sql === '') {
            $constraints = $this->QBOptions['constraints'] ?? [];

            if (empty($constraints)) {
                $fieldNames = array_map(static fn ($columnName): string => trim($columnName, '"'), $keys);

                $uniqueIndexes = array_filter($this->db->getIndexData($table), static function ($index) use ($fieldNames): bool {
                    $hasAllFields = count(array_intersect($index->fields, $fieldNames)) === count($index->fields);

                    return ($index->type === 'PRIMARY' || $index->type === 'UNIQUE') && $hasAllFields;
                });

                // only take first index
                foreach ($uniqueIndexes as $index) {
                    $constraints = $index->fields;
                    break;
                }

                $constraints = $this->onConstraint($constraints)->QBOptions['constraints'] ?? [];
            }

            if (empty($constraints)) {
                if ($this->db->DBDebug) {
                    throw new DatabaseException('No constraint found for upsert.');
                }

                return ''; // @codeCoverageIgnore
            }

            $alias = $this->QBOptions['alias'] ?? '"_upsert"';

            $updateFields = $this->QBOptions['updateFields'] ?? $this->updateFields($keys, false, $constraints)->QBOptions['updateFields'] ?? [];

            $sql = 'MERGE INTO ' . $table . "\nUSING (\n{:_table_:}";

            $sql .= ") {$alias}\nON (";

            $sql .= implode(
                ' AND ',
                array_map(
                    static fn ($key, $value) => (
                        ($value instanceof RawSql && is_string($key))
                        ?
                        $table . '.' . $key . ' = ' . $value
                        :
                        (
                            $value instanceof RawSql
                            ?
                            $value
                            :
                            $table . '.' . $value . ' = ' . $alias . '.' . $value
                        )
                    ),
                    array_keys($constraints),
                    $constraints,
                ),
            ) . ")\n";

            $sql .= "WHEN MATCHED THEN UPDATE SET\n";

            $sql .= implode(
                ",\n",
                array_map(
                    static fn ($key, $value): string => $key . ($value instanceof RawSql ?
                    " = {$value}" :
                    " = {$alias}.{$value}"),
                    array_keys($updateFields),
                    $updateFields,
                ),
            );

            $sql .= "\nWHEN NOT MATCHED THEN INSERT (" . implode(', ', $keys) . ")\nVALUES ";

            $sql .= (' ('
                . implode(', ', array_map(static fn ($columnName): string => "{$alias}.{$columnName}", $keys))
                . ')');

            $this->QBOptions['sql'] = $sql;
        }

        if (isset($this->QBOptions['setQueryAsData'])) {
            $data = $this->QBOptions['setQueryAsData'];
        } else {
            $data = implode(
                " FROM DUAL UNION ALL\n",
                array_map(
                    static fn ($value): string => 'SELECT ' . implode(', ', array_map(
                        static fn ($key, $index): string => $index . ' ' . $key,
                        $keys,
                        $value,
                    )),
                    $values,
                ),
            ) . " FROM DUAL\n";
        }

        return str_replace('{:_table_:}', $data, $sql);
    }

    /**
     * Generates a platform-specific batch update string from the supplied data
     */
    protected function _deleteBatch(string $table, array $keys, array $values): string
    {
        $sql = $this->QBOptions['sql'] ?? '';

        // if this is the first iteration of batch then we need to build skeleton sql
        if ($sql === '') {
            $constraints = $this->QBOptions['constraints'] ?? [];

            if ($constraints === []) {
                if ($this->db->DBDebug) {
                    throw new DatabaseException('You must specify a constraint to match on for batch deletes.'); // @codeCoverageIgnore
                }

                return ''; // @codeCoverageIgnore
            }

            $alias = $this->QBOptions['alias'] ?? '_u';

            $sql = 'DELETE ' . $table . "\n";

            $sql .= "WHERE EXISTS (SELECT * FROM (\n{:_table_:}";

            $sql .= ') ' . $alias . "\n";

            $sql .= 'WHERE ' . implode(
                ' AND ',
                array_map(
                    static fn ($key, $value) => (
                        $value instanceof RawSql ?
                        $value :
                        (
                            is_string($key) ?
                            $table . '.' . $key . ' = ' . $alias . '.' . $value :
                            $table . '.' . $value . ' = ' . $alias . '.' . $value
                        )
                    ),
                    array_keys($constraints),
                    $constraints,
                ),
            );

            // convert binds in where
            foreach ($this->QBWhere as $key => $where) {
                foreach ($this->binds as $field => $bind) {
                    $this->QBWhere[$key]['condition'] = str_replace(':' . $field . ':', $bind[0], $where['condition']);
                }
            }

            $sql .= ' ' . str_replace(
                'WHERE ',
                'AND ',
                $this->compileWhereHaving('QBWhere'),
            ) . ')';

            $this->QBOptions['sql'] = $sql;
        }

        if (isset($this->QBOptions['setQueryAsData'])) {
            $data = $this->QBOptions['setQueryAsData'];
        } else {
            $data = implode(
                " FROM DUAL UNION ALL\n",
                array_map(
                    static fn ($value): string => 'SELECT ' . implode(', ', array_map(
                        static fn ($key, $index): string => $index . ' ' . $key,
                        $keys,
                        $value,
                    )),
                    $values,
                ),
            ) . " FROM DUAL\n";
        }

        return str_replace('{:_table_:}', $data, $sql);
    }

    /**
     * Gets column names from a select query
     */
    protected function fieldsFromQuery(string $sql): array
    {
        return $this->db->query('SELECT * FROM (' . $sql . ') "_u_" WHERE ROWNUM = 1')->getFieldNames();
    }
}
