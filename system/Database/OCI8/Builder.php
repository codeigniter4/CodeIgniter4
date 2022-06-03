<?php

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
        $insertKeys    = implode(', ', $keys);
        $hasPrimaryKey = in_array('PRIMARY', array_column($this->db->getIndexData($table), 'type'), true);

        // ORA-00001 measures
        if ($hasPrimaryKey) {
            $sql               = 'INSERT INTO ' . $table . ' (' . $insertKeys . ") \n SELECT * FROM (\n";
            $selectQueryValues = [];

            foreach ($values as $value) {
                $selectValues        = implode(',', array_map(static fn ($value, $key) => $value . ' as ' . $key, explode(',', substr(substr($value, 1), 0, -1)), $keys));
                $selectQueryValues[] = 'SELECT ' . $selectValues . ' FROM DUAL';
            }

            return $sql . implode("\n UNION ALL \n", $selectQueryValues) . "\n)";
        }

        $sql = "INSERT ALL\n";

        foreach ($values as $value) {
            $sql .= '	INTO ' . $table . ' (' . $insertKeys . ') VALUES ' . $value . "\n";
        }

        return $sql . 'SELECT * FROM DUAL';
    }

    /**
     * Generates a platform-specific replace string from the supplied data
     */
    protected function _replace(string $table, array $keys, array $values): string
    {
        $fieldNames = array_map(static fn ($columnName) => trim($columnName, '"'), $keys);

        $uniqueIndexes = array_filter($this->db->getIndexData($table), static function ($index) use ($fieldNames) {
            $hasAllFields = count(array_intersect($index->fields, $fieldNames)) === count($index->fields);

            return ($index->type === 'PRIMARY') && $hasAllFields;
        });
        $replaceableFields = array_filter($keys, static function ($columnName) use ($uniqueIndexes) {
            foreach ($uniqueIndexes as $index) {
                if (in_array(trim($columnName, '"'), $index->fields, true)) {
                    return false;
                }
            }

            return true;
        });

        $sql = 'MERGE INTO ' . $table . "\n USING (SELECT ";

        $sql .= implode(', ', array_map(static fn ($columnName, $value) => $value . ' ' . $columnName, $keys, $values));

        $sql .= ' FROM DUAL) "_replace" ON ( ';

        $onList   = [];
        $onList[] = '1 != 1';

        foreach ($uniqueIndexes as $index) {
            $onList[] = '(' . implode(' AND ', array_map(static fn ($columnName) => $table . '."' . $columnName . '" = "_replace"."' . $columnName . '"', $index->fields)) . ')';
        }

        $sql .= implode(' OR ', $onList) . ') WHEN MATCHED THEN UPDATE SET ';

        $sql .= implode(', ', array_map(static fn ($columnName) => $columnName . ' = "_replace".' . $columnName, $replaceableFields));

        $sql .= ' WHEN NOT MATCHED THEN INSERT (' . implode(', ', $replaceableFields) . ') VALUES ';

        return $sql . (' (' . implode(', ', array_map(static fn ($columnName) => '"_replace".' . $columnName, $replaceableFields)) . ')');
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
     * @throws DatabaseException
     *
     * @return mixed
     */
    public function delete($where = '', ?int $limit = null, bool $resetData = true)
    {
        if (! empty($limit)) {
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
        $limitTemplateQuery = 'SELECT * FROM (SELECT INNER_QUERY.*, ROWNUM RNUM FROM (%s) INNER_QUERY WHERE ROWNUM < %d)' . ($offset ? ' WHERE RNUM >= %d' : '');

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
}
