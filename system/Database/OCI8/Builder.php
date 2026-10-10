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
use stdClass;

/**
 * Builder for OCI8
 *
 * @extends BaseBuilder<Connection>
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
     * @var list<string>
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
     * @param array<array-key, mixed>|RawSql|string $where
     *
     * @return bool|string
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

        // OFFSET-FETCH can be used only with the ORDER BY clause
        if (! is_array($this->QBOrderBy) || $this->QBOrderBy === []) {
            $sql .= ' ORDER BY 1';
        }

        return $sql . ' OFFSET ' . $offset . ' ROWS FETCH NEXT ' . $this->QBLimit . ' ROWS ONLY';
    }

    /**
     * Compile the SELECT lock clause.
     */
    protected function compileSelectLock(): string
    {
        if ($this->QBSelectLock === null) {
            return parent::compileSelectLock();
        }

        if ($this->QBSelectLock === self::SELECT_LOCK_SHARED) {
            throw new DatabaseException('OCI8 does not support sharedLock().');
        }

        if ($this->QBLimit !== false || $this->QBOffset) {
            throw new DatabaseException('OCI8 does not support lockForUpdate() with limit() or offset().');
        }

        if ($this->QBDistinct || $this->QBGroupBy !== [] || $this->QBHaving !== [] || $this->QBSelectUsesAggregate) {
            throw new DatabaseException('OCI8 does not support lockForUpdate() with distinct(), groupBy(), having(), or aggregate helper selections.');
        }

        return parent::compileSelectLock();
    }

    /**
     * Ensures the current driver supports explaining Query Builder selects.
     */
    protected function assertExplainSupported(): never
    {
        throw new DatabaseException('OCI8 does not support explain().');
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
                    static fn ($key, $value): RawSql|string => (
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
            $updateFields = $this->QBOptions['updateFields'] ?? [];
            $lengths      = $this->longestValues($keys, $values);

            $data = implode(
                " UNION ALL\n",
                array_map(
                    fn (array $value): string => sprintf('SELECT %s FROM DUAL', implode(', ', array_map(
                        fn (string $key, float|int|string $index): string => sprintf(
                            '%s %s',
                            $this->cast((string) $index, $this->getSourceField($table, $key, $updateFields), $lengths[$key]),
                            $key,
                        ),
                        $keys,
                        $value,
                    ))),
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

            if ($constraints === []) {
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

            if ($constraints === []) {
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
                    static fn ($key, $value): RawSql|string => (
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
                    static fn ($key, $value): RawSql|string => (
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
            $this->convertWhereBindsForBatch();

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
            $constraints = $this->QBOptions['constraints'] ?? [];
            $lengths     = $this->longestValues($keys, $values);

            $data = implode(
                " FROM DUAL UNION ALL\n",
                array_map(
                    fn (array $value): string => sprintf('SELECT %s', implode(', ', array_map(
                        fn (string $key, float|int|string $index): string => sprintf(
                            '%s %s',
                            $this->cast((string) $index, $this->getSourceField($table, $key, $constraints), $lengths[$key]),
                            $key,
                        ),
                        $keys,
                        $value,
                    ))),
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

    /**
     * Returns the longest escaped value per key across the batch.
     *
     * @param list<string>                 $keys
     * @param list<list<float|int|string>> $values
     *
     * @return array<string, int>
     */
    private function longestValues(array $keys, array $values): array
    {
        $lengths = [];

        foreach ($keys as $i => $key) {
            $lengths[$key] = max(array_map(static fn (array $row): int => mb_strlen((string) $row[$i]), $values));
        }

        return $lengths;
    }

    /**
     * Returns the field shared by every destination column fed from the source key, or null when their types differ.
     *
     * @param array<array-key, RawSql|string> $map Destination column to source key.
     */
    private function getSourceField(string $table, string $key, array $map): ?stdClass
    {
        $columns = array_filter(array_keys($map, $key, true), is_string(...));

        if ($columns === []) {
            return $this->getField($table, $key);
        }

        $fields = array_map(fn (string $column): ?stdClass => $this->getField($table, $column), $columns);
        $types  = array_unique(array_map(static fn (?stdClass $field): string => $field instanceof stdClass ? $field->type . '(' . $field->max_length . ')' : '', $fields));

        return count($types) === 1 ? reset($fields) : null;
    }

    /**
     * Returns the literal converted to the field type, or unchanged when the field is unknown or a LOB.
     *
     * @param int $length Longest value for the key in the batch.
     */
    private function cast(string $value, ?stdClass $field, int $length): string
    {
        if (! $field instanceof stdClass) {
            return $value;
        }

        return match ($field->type) {
            // Every row gets the same fixed width, wide enough for the longest value, so the UNION ALL stays CHAR and nothing is truncated.
            'CHAR', 'NCHAR'                                             => sprintf('CAST(%s AS %s(%d))', $value, $field->type, max((int) $field->max_length, $length)),
            'VARCHAR2'                                                  => sprintf('TO_CHAR(%s)', $value),
            'NVARCHAR2'                                                 => sprintf('TO_NCHAR(%s)', $value),
            'RAW', 'LONG RAW', 'CLOB', 'NCLOB', 'BLOB', 'BFILE', 'LONG' => $value,
            default                                                     => sprintf('CAST(%s AS %s)', $value, $field->type),
        };
    }

    /**
     * @param string $table     Protected table name.
     * @param string $fieldName Field name. May be protected.
     */
    private function getField(string $table, string $fieldName): ?stdClass
    {
        $fieldName = trim($fieldName, $this->db->escapeChar);

        if (! isset($this->QBOptions['fields'][$table])) {
            $this->QBOptions['fields'][$table] = [];

            foreach ($this->db->getFieldData($table) as $field) {
                $this->QBOptions['fields'][$table][$field->name] = $field;
            }
        }

        return $this->QBOptions['fields'][$table][$fieldName] ?? null;
    }
}
