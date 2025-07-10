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

namespace CodeIgniter\Database\Postgre;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\RawSql;
use CodeIgniter\Exceptions\InvalidArgumentException;

/**
 * Builder for Postgre
 */
class Builder extends BaseBuilder
{
    /**
     * ORDER BY random keyword
     *
     * @var array
     */
    protected $randomKeyword = [
        'RANDOM()',
    ];

    /**
     * Specifies which sql statements
     * support the ignore option.
     *
     * @var array<string, string>
     */
    protected $supportedIgnoreStatements = [
        'insert' => 'ON CONFLICT DO NOTHING',
    ];

    /**
     * Checks if the ignore option is supported by
     * the Database Driver for the specific statement.
     *
     * @return string
     */
    protected function compileIgnore(string $statement)
    {
        $sql = parent::compileIgnore($statement);

        if (! empty($sql)) {
            $sql = ' ' . trim($sql);
        }

        return $sql;
    }

    /**
     * ORDER BY
     *
     * @param string $direction ASC, DESC or RANDOM
     *
     * @return BaseBuilder
     */
    public function orderBy(string $orderBy, string $direction = '', ?bool $escape = null)
    {
        $direction = strtoupper(trim($direction));
        if ($direction === 'RANDOM') {
            if (ctype_digit($orderBy)) {
                $orderBy = (float) ($orderBy > 1 ? "0.{$orderBy}" : $orderBy);
            }

            if (is_float($orderBy)) {
                $this->db->simpleQuery("SET SEED {$orderBy}");
            }

            $orderBy   = $this->randomKeyword[0];
            $direction = '';
            $escape    = false;
        }

        return parent::orderBy($orderBy, $direction, $escape);
    }

    /**
     * Increments a numeric column by the specified value.
     *
     * @return mixed
     *
     * @throws DatabaseException
     */
    public function increment(string $column, int $value = 1)
    {
        $column = $this->db->protectIdentifiers($column);

        $sql = $this->_update($this->QBFrom[0], [$column => "to_number({$column}, '9999999') + {$value}"]);

        if (! $this->testMode) {
            $this->resetWrite();

            return $this->db->query($sql, $this->binds, false);
        }

        return true;
    }

    /**
     * Decrements a numeric column by the specified value.
     *
     * @return mixed
     *
     * @throws DatabaseException
     */
    public function decrement(string $column, int $value = 1)
    {
        $column = $this->db->protectIdentifiers($column);

        $sql = $this->_update($this->QBFrom[0], [$column => "to_number({$column}, '9999999') - {$value}"]);

        if (! $this->testMode) {
            $this->resetWrite();

            return $this->db->query($sql, $this->binds, false);
        }

        return true;
    }

    /**
     * Compiles an replace into string and runs the query.
     * Because PostgreSQL doesn't support the replace into command,
     * we simply do a DELETE and an INSERT on the first key/value
     * combo, assuming that it's either the primary key or a unique key.
     *
     * @param array|null $set An associative array of insert values
     *
     * @return mixed
     *
     * @throws DatabaseException
     */
    public function replace(?array $set = null)
    {
        if ($set !== null) {
            $this->set($set);
        }

        if ($this->QBSet === []) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('You must use the "set" method to update an entry.');
            }

            return false; // @codeCoverageIgnore
        }

        $table = $this->QBFrom[0];
        $set   = $this->binds;

        array_walk($set, static function (array &$item): void {
            $item = $item[0];
        });

        $key   = array_key_first($set);
        $value = $set[$key];

        $builder = $this->db->table($table);
        $exists  = $builder->where($key, $value, true)->get()->getFirstRow();

        if (empty($exists) && $this->testMode) {
            $result = $this->getCompiledInsert();
        } elseif (empty($exists)) {
            $result = $builder->insert($set);
        } elseif ($this->testMode) {
            $result = $this->where($key, $value, true)->getCompiledUpdate();
        } else {
            array_shift($set);
            $result = $builder->where($key, $value, true)->update($set);
        }

        unset($builder);
        $this->resetWrite();
        $this->binds = [];

        return $result;
    }

    /**
     * Generates a platform-specific insert string from the supplied data
     */
    protected function _insert(string $table, array $keys, array $unescapedKeys): string
    {
        return trim(sprintf('INSERT INTO %s (%s) VALUES (%s) %s', $table, implode(', ', $keys), implode(', ', $unescapedKeys), $this->compileIgnore('insert')));
    }

    /**
     * Generates a platform-specific insert string from the supplied data.
     */
    protected function _insertBatch(string $table, array $keys, array $values): string
    {
        $sql = $this->QBOptions['sql'] ?? '';

        // if this is the first iteration of batch then we need to build skeleton sql
        if ($sql === '') {
            $sql = 'INSERT INTO ' . $table . '(' . implode(', ', $keys) . ")\n{:_table_:}\n";

            $sql .= $this->compileIgnore('insert');

            $this->QBOptions['sql'] = $sql;
        }

        if (isset($this->QBOptions['setQueryAsData'])) {
            $data = $this->QBOptions['setQueryAsData'];
        } else {
            $data = 'VALUES ' . implode(', ', $this->formatValues($values));
        }

        return str_replace('{:_table_:}', $data, $sql);
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
        if ($limit !== null && $limit !== 0 || ! empty($this->QBLimit)) {
            throw new DatabaseException('PostgreSQL does not allow LIMITs on DELETE queries.');
        }

        return parent::delete($where, $limit, $resetData);
    }

    /**
     * Generates a platform-specific LIMIT clause.
     */
    protected function _limit(string $sql, bool $offsetIgnore = false): string
    {
        return $sql . ' LIMIT ' . $this->QBLimit . ($this->QBOffset ? " OFFSET {$this->QBOffset}" : '');
    }

    /**
     * Generates a platform-specific update string from the supplied data
     *
     * @throws DatabaseException
     */
    protected function _update(string $table, array $values): string
    {
        if (! empty($this->QBLimit)) {
            throw new DatabaseException('Postgres does not support LIMITs with UPDATE queries.');
        }

        $this->QBOrderBy = [];

        return parent::_update($table, $values);
    }

    /**
     * Generates a platform-specific delete string from the supplied data
     */
    protected function _delete(string $table): string
    {
        $this->QBLimit = false;

        return parent::_delete($table);
    }

    /**
     * Generates a platform-specific truncate string from the supplied data
     *
     * If the database does not support the truncate() command,
     * then this method maps to 'DELETE FROM table'
     */
    protected function _truncate(string $table): string
    {
        return 'TRUNCATE ' . $table . ' RESTART IDENTITY';
    }

    /**
     * Platform independent LIKE statement builder.
     *
     * In PostgreSQL, the ILIKE operator will perform case insensitive
     * searches according to the current locale.
     *
     * @see https://www.postgresql.org/docs/9.2/static/functions-matching.html
     */
    protected function _like_statement(?string $prefix, string $column, ?string $not, string $bind, bool $insensitiveSearch = false): string
    {
        $op = $insensitiveSearch ? 'ILIKE' : 'LIKE';

        return "{$prefix} {$column} {$not} {$op} :{$bind}:";
    }

    /**
     * Generates the JOIN portion of the query
     *
     * @param RawSql|string $cond
     *
     * @return BaseBuilder
     */
    public function join(string $table, $cond, string $type = '', ?bool $escape = null)
    {
        if (! in_array('FULL OUTER', $this->joinTypes, true)) {
            $this->joinTypes = array_merge($this->joinTypes, ['FULL OUTER']);
        }

        return parent::join($table, $cond, $type, $escape);
    }

    /**
     * Generates a platform-specific batch update string from the supplied data
     *
     * @used-by batchExecute()
     *
     * @param string                 $table  Protected table name
     * @param list<string>           $keys   QBKeys
     * @param list<list<int|string>> $values QBSet
     */
    protected function _updateBatch(string $table, array $keys, array $values): string
    {
        $sql = $this->QBOptions['sql'] ?? '';

        // if this is the first iteration of batch then we need to build skeleton sql
        if ($sql === '') {
            $constraints = $this->QBOptions['constraints'] ?? [];

            if ($constraints === []) {
                if ($this->db->DBDebug) {
                    throw new DatabaseException('You must specify a constraint to match on for batch updates.'); // @codeCoverageIgnore
                }

                return ''; // @codeCoverageIgnore
            }

            $updateFields = $this->QBOptions['updateFields'] ??
                $this->updateFields($keys, false, $constraints)->QBOptions['updateFields'] ??
                [];

            $alias = $this->QBOptions['alias'] ?? '_u';

            $sql = 'UPDATE ' . $this->compileIgnore('update') . $table . "\n";

            $sql .= "SET\n";

            $that = $this;
            $sql .= implode(
                ",\n",
                array_map(
                    static fn ($key, $value): string => $key . ($value instanceof RawSql ?
                            ' = ' . $value :
                            ' = ' . $that->cast($alias . '.' . $value, $that->getFieldType($table, $key))),
                    array_keys($updateFields),
                    $updateFields,
                ),
            ) . "\n";

            $sql .= "FROM (\n{:_table_:}";

            $sql .= ') ' . $alias . "\n";

            $sql .= 'WHERE ' . implode(
                ' AND ',
                array_map(
                    static function ($key, $value) use ($table, $alias, $that): string|RawSql {
                        if ($value instanceof RawSql && is_string($key)) {
                            return $table . '.' . $key . ' = ' . $value;
                        }

                        if ($value instanceof RawSql) {
                            return $value;
                        }

                        return $table . '.' . $value . ' = '
                            . $that->cast($alias . '.' . $value, $that->getFieldType($table, $value));
                    },
                    array_keys($constraints),
                    $constraints,
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
                    )),
                    $values,
                ),
            ) . "\n";
        }

        return str_replace('{:_table_:}', $data, $sql);
    }

    /**
     * Returns cast expression.
     *
     * @TODO move this to BaseBuilder in 4.5.0
     */
    private function cast(string $expression, ?string $type): string
    {
        return ($type === null) ? $expression : 'CAST(' . $expression . ' AS ' . strtoupper($type) . ')';
    }

    /**
     * Returns the filed type from database meta data.
     *
     * @param string $table     Protected table name.
     * @param string $fieldName Field name. May be protected.
     */
    private function getFieldType(string $table, string $fieldName): ?string
    {
        $fieldName = trim($fieldName, $this->db->escapeChar);

        if (! isset($this->QBOptions['fieldTypes'][$table])) {
            $this->QBOptions['fieldTypes'][$table] = [];

            foreach ($this->db->getFieldData($table) as $field) {
                $type = $field->type;

                // If `character` (or `char`) lacks a specifier, it is equivalent
                // to `character(1)`.
                // See https://www.postgresql.org/docs/current/datatype-character.html
                if ($field->type === 'character') {
                    $type = $field->type . '(' . $field->max_length . ')';
                }

                $this->QBOptions['fieldTypes'][$table][$field->name] = $type;
            }
        }

        return $this->QBOptions['fieldTypes'][$table][$fieldName] ?? null;
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
            $fieldNames = array_map(static fn ($columnName): string => trim($columnName, '"'), $keys);

            $constraints = $this->QBOptions['constraints'] ?? [];

            if (empty($constraints)) {
                $allIndexes = array_filter($this->db->getIndexData($table), static function ($index) use ($fieldNames): bool {
                    $hasAllFields = count(array_intersect($index->fields, $fieldNames)) === count($index->fields);

                    return ($index->type === 'UNIQUE' || $index->type === 'PRIMARY') && $hasAllFields;
                });

                foreach ($allIndexes as $index) {
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

            // in value set - replace null with DEFAULT where constraint is presumed not null
            // autoincrement identity field must use DEFAULT and not NULL
            // this could be removed in favour of leaving to developer but does make things easier and function like other DBMS
            foreach ($constraints as $constraint) {
                $key = array_search(trim((string) $constraint, '"'), $fieldNames, true);

                if ($key !== false) {
                    foreach ($values as $arrayKey => $value) {
                        if (strtoupper((string) $value[$key]) === 'NULL') {
                            $values[$arrayKey][$key] = 'DEFAULT';
                        }
                    }
                }
            }

            $alias = $this->QBOptions['alias'] ?? '"excluded"';

            if (strtolower($alias) !== '"excluded"') {
                throw new InvalidArgumentException('Postgres alias is always named "excluded". A custom alias cannot be used.');
            }

            $updateFields = $this->QBOptions['updateFields'] ?? $this->updateFields($keys, false, $constraints)->QBOptions['updateFields'] ?? [];

            $sql = 'INSERT INTO ' . $table . ' (';

            $sql .= implode(', ', $keys);

            $sql .= ")\n";

            $sql .= '{:_table_:}';

            $sql .= 'ON CONFLICT(' . implode(',', $constraints) . ")\n";

            $sql .= "DO UPDATE SET\n";

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

            $this->QBOptions['sql'] = $sql;
        }

        if (isset($this->QBOptions['setQueryAsData'])) {
            $data = $this->QBOptions['setQueryAsData'];
        } else {
            $data = 'VALUES ' . implode(', ', $this->formatValues($values)) . "\n";
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

            $sql = 'DELETE FROM ' . $table . "\n";

            $sql .= "USING (\n{:_table_:}";

            $sql .= ') ' . $alias . "\n";

            $that = $this;
            $sql .= 'WHERE ' . implode(
                ' AND ',
                array_map(
                    static function ($key, $value) use ($table, $alias, $that): RawSql|string {
                        if ($value instanceof RawSql) {
                            return $value;
                        }

                        if (is_string($key)) {
                            return $table . '.' . $key . ' = '
                                . $that->cast(
                                    $alias . '.' . $value,
                                    $that->getFieldType($table, $key),
                                );
                        }

                        return $table . '.' . $value . ' = ' . $alias . '.' . $value;
                    },
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
                    )),
                    $values,
                ),
            ) . "\n";
        }

        return str_replace('{:_table_:}', $data, $sql);
    }
}
