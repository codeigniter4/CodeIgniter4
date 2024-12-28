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

namespace CodeIgniter\Database\SQLSRV;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Database\RawSql;
use CodeIgniter\Database\ResultInterface;
use Config\Feature;

/**
 * Builder for SQLSRV
 *
 * @todo auto check for TextCastToInt
 * @todo auto check for InsertIndexValue
 * @todo replace: delete index entries before insert
 */
class Builder extends BaseBuilder
{
    /**
     * ORDER BY random keyword
     *
     * @var array
     */
    protected $randomKeyword = [
        'NEWID()',
        'RAND(%d)',
    ];

    /**
     * Quoted identifier flag
     *
     * Whether to use SQL-92 standard quoted identifier
     * (double quotes) or brackets for identifier escaping.
     *
     * @var bool
     */
    protected $_quoted_identifier = true;

    /**
     * Handle increment/decrement on text
     *
     * @var bool
     */
    public $castTextToInt = true;

    /**
     * Handle IDENTITY_INSERT property/
     *
     * @var bool
     */
    public $keyPermission = false;

    /**
     * Groups tables in FROM clauses if needed, so there is no confusion
     * about operator precedence.
     */
    protected function _fromTables(): string
    {
        $from = [];

        foreach ($this->QBFrom as $value) {
            $from[] = str_starts_with($value, '(SELECT') ? $value : $this->getFullName($value);
        }

        return implode(', ', $from);
    }

    /**
     * Generates a platform-specific truncate string from the supplied data
     *
     * If the database does not support the truncate() command,
     * then this method maps to 'DELETE FROM table'
     */
    protected function _truncate(string $table): string
    {
        return 'TRUNCATE TABLE ' . $this->getFullName($table);
    }

    /**
     * Generates the JOIN portion of the query
     *
     * @param RawSql|string $cond
     *
     * @return $this
     */
    public function join(string $table, $cond, string $type = '', ?bool $escape = null)
    {
        if ($type !== '') {
            $type = strtoupper(trim($type));

            if (! in_array($type, $this->joinTypes, true)) {
                $type = '';
            } else {
                $type .= ' ';
            }
        }

        // Extract any aliases that might exist. We use this information
        // in the protectIdentifiers to know whether to add a table prefix
        $this->trackAliases($table);

        if (! is_bool($escape)) {
            $escape = $this->db->protectIdentifiers;
        }

        if (! $this->hasOperator($cond)) {
            $cond = ' USING (' . ($escape ? $this->db->escapeIdentifiers($cond) : $cond) . ')';
        } elseif ($escape === false) {
            $cond = ' ON ' . $cond;
        } else {
            // Split multiple conditions
            if (preg_match_all('/\sAND\s|\sOR\s/i', $cond, $joints, PREG_OFFSET_CAPTURE)) {
                $conditions = [];
                $joints     = $joints[0];
                array_unshift($joints, ['', 0]);

                for ($i = count($joints) - 1, $pos = strlen($cond); $i >= 0; $i--) {
                    $joints[$i][1] += strlen($joints[$i][0]); // offset
                    $conditions[$i] = substr($cond, $joints[$i][1], $pos - $joints[$i][1]);
                    $pos            = $joints[$i][1] - strlen($joints[$i][0]);
                    $joints[$i]     = $joints[$i][0];
                }

                ksort($conditions);
            } else {
                $conditions = [$cond];
                $joints     = [''];
            }

            $cond = ' ON ';

            foreach ($conditions as $i => $condition) {
                $operator = $this->getOperator($condition);

                // Workaround for BETWEEN
                if ($operator === false) {
                    $cond .= $joints[$i] . $condition;

                    continue;
                }

                $cond .= $joints[$i];
                $cond .= preg_match('/(\(*)?([\[\]\w\.\'-]+)' . preg_quote($operator, '/') . '(.*)/i', $condition, $match) ? $match[1] . $this->db->protectIdentifiers($match[2]) . $operator . $this->db->protectIdentifiers($match[3]) : $condition;
            }
        }

        // Do we want to escape the table name?
        if ($escape === true) {
            $table = $this->db->protectIdentifiers($table, true, null, false);
        }

        // Assemble the JOIN statement
        $this->QBJoin[] = $type . 'JOIN ' . $this->getFullName($table) . $cond;

        return $this;
    }

    /**
     * Generates a platform-specific insert string from the supplied data
     *
     * @todo implement check for this instead static $insertKeyPermission
     */
    protected function _insert(string $table, array $keys, array $unescapedKeys): string
    {
        $fullTableName = $this->getFullName($table);

        // insert statement
        $statement = 'INSERT INTO ' . $fullTableName . ' (' . implode(',', $keys) . ') VALUES (' . implode(', ', $unescapedKeys) . ')';

        return $this->keyPermission ? $this->addIdentity($fullTableName, $statement) : $statement;
    }

    /**
     * Insert batch statement
     *
     * Generates a platform-specific insert string from the supplied data.
     */
    protected function _insertBatch(string $table, array $keys, array $values): string
    {
        $sql = $this->QBOptions['sql'] ?? '';

        // if this is the first iteration of batch then we need to build skeleton sql
        if ($sql === '') {
            $sql = 'INSERT ' . $this->compileIgnore('insert') . 'INTO ' . $this->getFullName($table)
                . ' (' . implode(', ', $keys) . ")\n{:_table_:}";

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
     * Generates a platform-specific update string from the supplied data
     */
    protected function _update(string $table, array $values): string
    {
        $valstr = [];

        foreach ($values as $key => $val) {
            $valstr[] = $key . ' = ' . $val;
        }

        $fullTableName = $this->getFullName($table);

        $statement = sprintf('UPDATE %s%s SET ', empty($this->QBLimit) ? '' : 'TOP(' . $this->QBLimit . ') ', $fullTableName);

        $statement .= implode(', ', $valstr)
            . $this->compileWhereHaving('QBWhere')
            . $this->compileOrderBy();

        return $this->keyPermission ? $this->addIdentity($fullTableName, $statement) : $statement;
    }

    /**
     * Increments a numeric column by the specified value.
     *
     * @return bool
     */
    public function increment(string $column, int $value = 1)
    {
        $column = $this->db->protectIdentifiers($column);

        if ($this->castTextToInt) {
            $values = [$column => "CONVERT(VARCHAR(MAX),CONVERT(INT,CONVERT(VARCHAR(MAX), {$column})) + {$value})"];
        } else {
            $values = [$column => "{$column} + {$value}"];
        }

        $sql = $this->_update($this->QBFrom[0], $values);

        if (! $this->testMode) {
            $this->resetWrite();

            return $this->db->query($sql, $this->binds, false);
        }

        return true;
    }

    /**
     * Decrements a numeric column by the specified value.
     *
     * @return bool
     */
    public function decrement(string $column, int $value = 1)
    {
        $column = $this->db->protectIdentifiers($column);

        if ($this->castTextToInt) {
            $values = [$column => "CONVERT(VARCHAR(MAX),CONVERT(INT,CONVERT(VARCHAR(MAX), {$column})) - {$value})"];
        } else {
            $values = [$column => "{$column} + {$value}"];
        }

        $sql = $this->_update($this->QBFrom[0], $values);

        if (! $this->testMode) {
            $this->resetWrite();

            return $this->db->query($sql, $this->binds, false);
        }

        return true;
    }

    /**
     * Get full name of the table
     */
    private function getFullName(string $table): string
    {
        $alias = '';

        if (str_contains($table, ' ')) {
            $alias = explode(' ', $table);
            $table = array_shift($alias);
            $alias = ' ' . implode(' ', $alias);
        }

        if ($this->db->escapeChar === '"') {
            if (str_contains($table, '.') && ! str_starts_with($table, '.') && ! str_ends_with($table, '.')) {
                $dbInfo   = explode('.', $table);
                $database = $this->db->getDatabase();
                $table    = $dbInfo[0];

                if (count($dbInfo) === 3) {
                    $database  = str_replace('"', '', $dbInfo[0]);
                    $schema    = str_replace('"', '', $dbInfo[1]);
                    $tableName = str_replace('"', '', $dbInfo[2]);
                } else {
                    $schema    = str_replace('"', '', $dbInfo[0]);
                    $tableName = str_replace('"', '', $dbInfo[1]);
                }

                return '"' . $database . '"."' . $schema . '"."' . str_replace('"', '', $tableName) . '"' . $alias;
            }

            return '"' . $this->db->getDatabase() . '"."' . $this->db->schema . '"."' . str_replace('"', '', $table) . '"' . $alias;
        }

        return '[' . $this->db->getDatabase() . '].[' . $this->db->schema . '].[' . str_replace('"', '', $table) . ']' . str_replace('"', '', $alias);
    }

    /**
     * Add permision statements for index value inserts
     */
    private function addIdentity(string $fullTable, string $insert): string
    {
        return 'SET IDENTITY_INSERT ' . $fullTable . " ON\n" . $insert . "\nSET IDENTITY_INSERT " . $fullTable . ' OFF';
    }

    /**
     * Local implementation of limit
     */
    protected function _limit(string $sql, bool $offsetIgnore = false): string
    {
        // SQL Server cannot handle `LIMIT 0`.
        // DatabaseException:
        //   [Microsoft][ODBC Driver 17 for SQL Server][SQL Server]The number of
        //   rows provided for a FETCH clause must be greater then zero.
        $limitZeroAsAll = config(Feature::class)->limitZeroAsAll ?? true;
        if (! $limitZeroAsAll && $this->QBLimit === 0) {
            return "SELECT * \nFROM " . $this->_fromTables() . ' WHERE 1=0 ';
        }

        if (empty($this->QBOrderBy)) {
            $sql .= ' ORDER BY (SELECT NULL) ';
        }

        if ($offsetIgnore) {
            $sql .= ' OFFSET 0 ';
        } else {
            $sql .= is_int($this->QBOffset) ? ' OFFSET ' . $this->QBOffset : ' OFFSET 0 ';
        }

        return $sql . ' ROWS FETCH NEXT ' . $this->QBLimit . ' ROWS ONLY ';
    }

    /**
     * Compiles a replace into string and runs the query
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

        $sql = $this->_replace($table, array_keys($this->QBSet), array_values($this->QBSet));

        $this->resetWrite();

        if ($this->testMode) {
            return $sql;
        }

        $this->db->simpleQuery('SET IDENTITY_INSERT ' . $this->getFullName($table) . ' ON');

        $result = $this->db->query($sql, $this->binds, false);
        $this->db->simpleQuery('SET IDENTITY_INSERT ' . $this->getFullName($table) . ' OFF');

        return $result;
    }

    /**
     * Generates a platform-specific replace string from the supplied data
     * on match delete and insert
     */
    protected function _replace(string $table, array $keys, array $values): string
    {
        // check whether the existing keys are part of the primary key.
        // if so then use them for the "ON" part and exclude them from the $values and $keys
        $pKeys     = $this->db->getIndexData($table);
        $keyFields = [];

        foreach ($pKeys as $key) {
            if ($key->type === 'PRIMARY') {
                $keyFields = array_merge($keyFields, $key->fields);
            }

            if ($key->type === 'UNIQUE') {
                $keyFields = array_merge($keyFields, $key->fields);
            }
        }

        // Get the unique field names
        $escKeyFields = array_map(fn (string $field): string => $this->db->protectIdentifiers($field), array_values(array_unique($keyFields)));

        // Get the binds
        $binds = $this->binds;
        array_walk($binds, static function (&$item): void {
            $item = $item[0];
        });

        // Get the common field and values from the keys data and index fields
        $common = array_intersect($keys, $escKeyFields);
        $bingo  = [];

        foreach ($common as $v) {
            $k = array_search($v, $keys, true);

            $bingo[$keys[$k]] = $binds[trim($values[$k], ':')];
        }

        // Querying existing data
        $builder = $this->db->table($table);

        foreach ($bingo as $k => $v) {
            $builder->where($k, $v);
        }

        $q = $builder->get()->getResult();

        // Delete entries if we find them
        if ($q !== []) {
            $delete = $this->db->table($table);

            foreach ($bingo as $k => $v) {
                $delete->where($k, $v);
            }

            $delete->delete();
        }

        return sprintf('INSERT INTO %s (%s) VALUES (%s);', $this->getFullName($table), implode(',', $keys), implode(',', $values));
    }

    /**
     * SELECT [MAX|MIN|AVG|SUM|COUNT]()
     *
     * Handle float return value
     *
     * @return BaseBuilder
     */
    protected function maxMinAvgSum(string $select = '', string $alias = '', string $type = 'MAX')
    {
        // int functions can be handled by parent
        if ($type !== 'AVG') {
            return parent::maxMinAvgSum($select, $alias, $type);
        }

        if ($select === '') {
            throw DataException::forEmptyInputGiven('Select');
        }

        if (str_contains($select, ',')) {
            throw DataException::forInvalidArgument('Column name not separated by comma');
        }

        if ($alias === '') {
            $alias = $this->createAliasFromTable(trim($select));
        }

        $sql = $type . '( CAST( ' . $this->db->protectIdentifiers(trim($select)) . ' AS FLOAT ) ) AS ' . $this->db->escapeIdentifiers(trim($alias));

        $this->QBSelect[]   = $sql;
        $this->QBNoEscape[] = null;

        return $this;
    }

    /**
     * "Count All" query
     *
     * Generates a platform-specific query string that counts all records in
     * the particular table
     *
     * @param bool $reset Are we want to clear query builder values?
     *
     * @return int|string when $test = true
     */
    public function countAll(bool $reset = true)
    {
        $table = $this->QBFrom[0];

        $sql = $this->countString . $this->db->escapeIdentifiers('numrows') . ' FROM ' . $this->getFullName($table);

        if ($this->testMode) {
            return $sql;
        }

        $query = $this->db->query($sql, null, false);
        if (empty($query->getResult())) {
            return 0;
        }

        $query = $query->getRow();

        if ($reset) {
            $this->resetSelect();
        }

        return (int) $query->numrows;
    }

    /**
     * Delete statement
     */
    protected function _delete(string $table): string
    {
        return 'DELETE' . (empty($this->QBLimit) ? '' : ' TOP (' . $this->QBLimit . ') ') . ' FROM ' . $this->getFullName($table) . $this->compileWhereHaving('QBWhere');
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
        $table = $this->db->protectIdentifiers($this->QBFrom[0], true, null, false);

        if ($where !== '') {
            $this->where($where);
        }

        if ($this->QBWhere === []) {
            if ($this->db->DBDebug) {
                throw new DatabaseException('Deletes are not allowed unless they contain a "where" or "like" clause.');
            }

            return false; // @codeCoverageIgnore
        }

        if ($limit !== null && $limit !== 0) {
            $this->QBLimit = $limit;
        }

        $sql = $this->_delete($table);

        if ($resetData) {
            $this->resetWrite();
        }

        return $this->testMode ? $sql : $this->db->query($sql, $this->binds, false);
    }

    /**
     * Compile the SELECT statement
     *
     * Generates a query string based on which functions were used.
     *
     * @param bool $selectOverride
     */
    protected function compileSelect($selectOverride = false): string
    {
        // Write the "select" portion of the query
        if ($selectOverride !== false) {
            $sql = $selectOverride;
        } else {
            $sql = (! $this->QBDistinct) ? 'SELECT ' : 'SELECT DISTINCT ';

            // SQL Server can't work with select * if group by is specified
            if (empty($this->QBSelect) && $this->QBGroupBy !== [] && is_array($this->QBGroupBy)) {
                foreach ($this->QBGroupBy as $field) {
                    $this->QBSelect[] = is_array($field) ? $field['field'] : $field;
                }
            }

            if (empty($this->QBSelect)) {
                $sql .= '*';
            } else {
                // Cycle through the "select" portion of the query and prep each column name.
                // The reason we protect identifiers here rather than in the select() function
                // is because until the user calls the from() function we don't know if there are aliases
                foreach ($this->QBSelect as $key => $val) {
                    $noEscape             = $this->QBNoEscape[$key] ?? null;
                    $this->QBSelect[$key] = $this->db->protectIdentifiers($val, false, $noEscape);
                }

                $sql .= implode(', ', $this->QBSelect);
            }
        }

        // Write the "FROM" portion of the query
        if ($this->QBFrom !== []) {
            $sql .= "\nFROM " . $this->_fromTables();
        }

        // Write the "JOIN" portion of the query
        if (! empty($this->QBJoin)) {
            $sql .= "\n" . implode("\n", $this->QBJoin);
        }

        $sql .= $this->compileWhereHaving('QBWhere')
            . $this->compileGroupBy()
            . $this->compileWhereHaving('QBHaving')
            . $this->compileOrderBy(); // ORDER BY

        // LIMIT
        $limitZeroAsAll = config(Feature::class)->limitZeroAsAll ?? true;
        if ($limitZeroAsAll) {
            if ($this->QBLimit) {
                $sql = $this->_limit($sql . "\n");
            }
        } elseif ($this->QBLimit !== false || $this->QBOffset) {
            $sql = $this->_limit($sql . "\n");
        }

        return $this->unionInjection($sql);
    }

    /**
     * Compiles the select statement based on the other functions called
     * and runs the query
     *
     * @return ResultInterface
     */
    public function get(?int $limit = null, int $offset = 0, bool $reset = true)
    {
        $limitZeroAsAll = config(Feature::class)->limitZeroAsAll ?? true;
        if ($limitZeroAsAll && $limit === 0) {
            $limit = null;
        }

        if ($limit !== null) {
            $this->limit($limit, $offset);
        }

        $result = $this->testMode ? $this->getCompiledSelect($reset) : $this->db->query($this->compileSelect(), $this->binds, false);

        if ($reset) {
            $this->resetSelect();

            // Clear our binds so we don't eat up memory
            $this->binds = [];
        }

        return $result;
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
            $fullTableName = $this->getFullName($table);

            $constraints = $this->QBOptions['constraints'] ?? [];

            $tableIdentity = $this->QBOptions['tableIdentity'] ?? '';
            $sql           = "SELECT name from syscolumns where id = Object_ID('" . $table . "') and colstat = 1";
            if (($query = $this->db->query($sql)) === false) {
                throw new DatabaseException('Failed to get table identity');
            }
            $query = $query->getResultObject();

            foreach ($query as $row) {
                $tableIdentity = '"' . $row->name . '"';
            }
            $this->QBOptions['tableIdentity'] = $tableIdentity;

            $identityInFields = in_array($tableIdentity, $keys, true);

            $fieldNames = array_map(static fn ($columnName) => trim($columnName, '"'), $keys);

            if (empty($constraints)) {
                $tableIndexes = $this->db->getIndexData($table);

                $uniqueIndexes = array_filter($tableIndexes, static function ($index) use ($fieldNames): bool {
                    $hasAllFields = count(array_intersect($index->fields, $fieldNames)) === count($index->fields);

                    return $index->type === 'PRIMARY' && $hasAllFields;
                });

                // if no primary found then look for unique - since indexes have no order
                if ($uniqueIndexes === []) {
                    $uniqueIndexes = array_filter($tableIndexes, static function ($index) use ($fieldNames): bool {
                        $hasAllFields = count(array_intersect($index->fields, $fieldNames)) === count($index->fields);

                        return $index->type === 'UNIQUE' && $hasAllFields;
                    });
                }

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

            $sql = 'MERGE INTO ' . $fullTableName . "\nUSING (\n";

            $sql .= '{:_table_:}';

            $sql .= ") {$alias} (";

            $sql .= implode(', ', $keys);

            $sql .= ')';

            $sql .= "\nON (";

            $sql .= implode(
                ' AND ',
                array_map(
                    static fn ($key, $value) => (
                        ($value instanceof RawSql && is_string($key))
                        ?
                        $fullTableName . '.' . $key . ' = ' . $value
                        :
                        (
                            $value instanceof RawSql
                            ?
                            $value
                            :
                            $fullTableName . '.' . $value . ' = ' . $alias . '.' . $value
                        )
                    ),
                    array_keys($constraints),
                    $constraints
                )
            ) . ")\n";

            $sql .= "WHEN MATCHED THEN UPDATE SET\n";

            $sql .= implode(
                ",\n",
                array_map(
                    static fn ($key, $value) => $key . ($value instanceof RawSql ?
                        ' = ' . $value :
                    " = {$alias}.{$value}"),
                    array_keys($updateFields),
                    $updateFields
                )
            );

            $sql .= "\nWHEN NOT MATCHED THEN INSERT (" . implode(', ', $keys) . ")\nVALUES ";

            $sql .= (
                '(' . implode(
                    ', ',
                    array_map(
                        static fn ($columnName) => $columnName === $tableIdentity
                    ? "CASE WHEN {$alias}.{$columnName} IS NULL THEN (SELECT "
                    . 'isnull(IDENT_CURRENT(\'' . $fullTableName . '\')+IDENT_INCR(\''
                    . $fullTableName . "'),1)) ELSE {$alias}.{$columnName} END"
                    : "{$alias}.{$columnName}",
                        $keys
                    )
                ) . ');'
            );

            $sql = $identityInFields ? $this->addIdentity($fullTableName, $sql) : $sql;

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
     * Gets column names from a select query
     */
    protected function fieldsFromQuery(string $sql): array
    {
        return $this->db->query('SELECT TOP 1 * FROM (' . $sql . ') _u_')->getFieldNames();
    }
}
