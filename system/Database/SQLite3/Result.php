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

namespace CodeIgniter\Database\SQLite3;

use Closure;
use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Entity\Entity;
use SQLite3;
use SQLite3Result;
use stdClass;

/**
 * Result for SQLite3
 *
 * @extends BaseResult<SQLite3, SQLite3Result>
 */
class Result extends BaseResult
{
    /**
     * Gets the number of fields in the result set.
     */
    public function getFieldCount(): int
    {
        return $this->resultID->numColumns();
    }

    /**
     * Generates an array of column names in the result set.
     */
    public function getFieldNames(): array
    {
        $fieldNames = [];

        for ($i = 0, $c = $this->getFieldCount(); $i < $c; $i++) {
            $fieldNames[] = $this->resultID->columnName($i);
        }

        return $fieldNames;
    }

    /**
     * Generates an array of objects representing field meta-data.
     */
    public function getFieldData(): array
    {
        static $dataTypes = [
            SQLITE3_INTEGER => 'integer',
            SQLITE3_FLOAT   => 'float',
            SQLITE3_TEXT    => 'text',
            SQLITE3_BLOB    => 'blob',
            SQLITE3_NULL    => 'null',
        ];

        $retVal = [];
        $this->resultID->fetchArray(SQLITE3_NUM);

        for ($i = 0, $c = $this->getFieldCount(); $i < $c; $i++) {
            $retVal[$i]             = new stdClass();
            $retVal[$i]->name       = $this->resultID->columnName($i);
            $type                   = $this->resultID->columnType($i);
            $retVal[$i]->type       = $type;
            $retVal[$i]->type_name  = $dataTypes[$type] ?? null;
            $retVal[$i]->max_length = null;
            $retVal[$i]->length     = null;
        }
        $this->resultID->reset();

        return $retVal;
    }

    /**
     * Frees the current result.
     *
     * @return void
     */
    public function freeResult()
    {
        if (is_object($this->resultID)) {
            $this->resultID->finalize();
            $this->resultID = false;
        }
    }

    /**
     * Moves the internal pointer to the desired offset. This is called
     * internally before fetching results to make sure the result set
     * starts at zero.
     *
     * @return bool
     *
     * @throws DatabaseException
     */
    public function dataSeek(int $n = 0)
    {
        if ($n !== 0) {
            throw new DatabaseException('SQLite3 doesn\'t support seeking to other offset.');
        }

        return $this->resultID->reset();
    }

    /**
     * Returns the result set as an array.
     *
     * Overridden by driver classes.
     *
     * @return array|false
     */
    protected function fetchAssoc()
    {
        return $this->resultID->fetchArray(SQLITE3_ASSOC);
    }

    /**
     * Returns the result set as an object.
     *
     * Overridden by child classes.
     *
     * @return Entity|false|object|stdClass
     */
    protected function fetchObject(string $className = 'stdClass')
    {
        // No native support for fetching rows as objects
        if (($row = $this->fetchAssoc()) === false) {
            return false;
        }

        if ($className === 'stdClass') {
            return (object) $row;
        }

        $classObj = new $className();

        if (is_subclass_of($className, Entity::class)) {
            return $classObj->injectRawData($row);
        }

        $classSet = Closure::bind(function ($key, $value) {
            $this->{$key} = $value;
        }, $classObj, $className);

        foreach (array_keys($row) as $key) {
            $classSet($key, $row[$key]);
        }

        return $classObj;
    }
}
