<?php

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
     * @var array
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
     * @throws DatabaseException
     *
     * @return mixed
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
     * @throws DatabaseException
     *
     * @return mixed
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
     * @throws DatabaseException
     *
     * @return mixed
     */
    public function replace(?array $set = null)
    {
        if ($set !== null) {
            $this->set($set);
        }

        if (! $this->QBSet) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('You must use the "set" method to update an entry.');
            }

            return false; // @codeCoverageIgnore
        }

        $table = $this->QBFrom[0];
        $set   = $this->binds;

        array_walk($set, static function (array &$item) {
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
        return trim(sprintf(
            'INSERT INTO %s (%s) VALUES %s %s',
            $table,
            implode(', ', $keys),
            implode(', ', $this->getValues($values)),
            $this->compileIgnore('insert')
        ));
    }

    /**
     * Generates a platform-specific upsertBatch string from the supplied data
     *
     * @throws DatabaseException
     */
    protected function _upsertBatch(string $table, array $keys, array $values): string
    {
        $fieldNames = array_map(static fn ($columnName) => trim($columnName, '"'), $keys);

        $constraints = $this->QBOptions['constraints'] ?? [];

        $updateFields = $this->QBOptions['updateFields'] ?? $fieldNames;

        if (empty($constraints)) {
            $allIndexes = array_filter($this->db->getIndexData($table), static function ($index) use ($fieldNames) {
                $hasAllFields = count(array_intersect($index->fields, $fieldNames)) === count($index->fields);

                return ($index->type === 'UNIQUE' || $index->type === 'PRIMARY') && $hasAllFields;
            });

            foreach (array_map(static fn ($index) => $index->fields, $allIndexes) as $index) {
                foreach ($index as $constraint) {
                    $constraints[] = $constraint;
                }
                // only one index can be used?
                break;
            }

            $this->QBOptions['constraints'] = $constraints;
        }

        // in value set - replace null with DEFAULT where constraint is presumed not null
        // autoincrement identity field must use DEFAULT and not NULL
        foreach ($constraints as $constraint) {
            $key = array_search($constraint, $fieldNames, true);

            if ($key !== false) {
                foreach ($values as $arrayKey => $value) {
                    if (strtoupper($value[$key]) === 'NULL') {
                        $values[$arrayKey][$key] = 'DEFAULT';
                    }
                }
            }
        }

        if (empty($constraints)) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('No constraint found for upsert.');
            }

            return ''; // @codeCoverageIgnore
        }

        $sql = 'INSERT INTO ' . $table . ' (';

        $sql .= implode(', ', array_map(static fn ($columnName) => $columnName, $keys));

        $sql .= ")\n";

        $sql .= 'VALUES ' . implode(', ', $this->getValues($values)) . "\n";

        $sql .= 'ON CONFLICT("' . implode('","', $constraints) . "\")\n";

        $sql .= "DO UPDATE SET\n";

        return $sql . implode(
            ",\n",
            array_map(
                static fn ($updateField) => '"' . $updateField . '" = "excluded"."' . $updateField . '"',
                $updateFields
            )
        );
    }

    /**
     * Compiles a delete string and runs the query
     *
     * @param mixed $where
     *
     * @throws DatabaseException
     *
     * @return mixed
     */
    public function delete($where = '', ?int $limit = null, bool $resetData = true)
    {
        if (! empty($limit) || ! empty($this->QBLimit)) {
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
     * Generates a platform-specific batch update string from the supplied data
     */
    protected function _updateBatch(string $table, array $values, string $index): string
    {
        $ids   = [];
        $final = [];

        foreach ($values as $val) {
            $ids[] = $val[$index];

            foreach (array_keys($val) as $field) {
                if ($field !== $index) {
                    $final[$field] ??= [];

                    $final[$field][] = "WHEN {$val[$index]} THEN {$val[$field]}";
                }
            }
        }

        $cases = '';

        foreach ($final as $k => $v) {
            $cases .= "{$k} = (CASE {$index}\n"
                    . implode("\n", $v)
                    . "\nELSE {$k} END), ";
        }

        $this->where("{$index} IN(" . implode(',', $ids) . ')', null, false);

        return "UPDATE {$table} SET " . substr($cases, 0, -2) . $this->compileWhereHaving('QBWhere');
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
        $op = $insensitiveSearch === true ? 'ILIKE' : 'LIKE';

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
}
