<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\MySQLi;

use CodeIgniter\Database\BaseResult;
use CodeIgniter\Entity\Entity;
use mysqli;
use mysqli_result;
use stdClass;

/**
 * Result for MySQLi
 *
 * @extends BaseResult<mysqli, mysqli_result>
 */
class Result extends BaseResult
{
    /**
     * Gets the number of fields in the result set.
     */
    public function getFieldCount(): int
    {
        return $this->resultID->field_count;
    }

    /**
     * Generates an array of column names in the result set.
     */
    public function getFieldNames(): array
    {
        $fieldNames = [];
        $this->resultID->field_seek(0);

        while ($field = $this->resultID->fetch_field()) {
            $fieldNames[] = $field->name;
        }

        return $fieldNames;
    }

    /**
     * Generates an array of objects representing field meta-data.
     */
    public function getFieldData(): array
    {
        static $dataTypes = [
            MYSQLI_TYPE_DECIMAL    => 'decimal',
            MYSQLI_TYPE_NEWDECIMAL => 'newdecimal',
            MYSQLI_TYPE_FLOAT      => 'float',
            MYSQLI_TYPE_DOUBLE     => 'double',

            MYSQLI_TYPE_BIT      => 'bit',
            MYSQLI_TYPE_SHORT    => 'short',
            MYSQLI_TYPE_LONG     => 'long',
            MYSQLI_TYPE_LONGLONG => 'longlong',
            MYSQLI_TYPE_INT24    => 'int24',

            MYSQLI_TYPE_YEAR => 'year',

            MYSQLI_TYPE_TIMESTAMP => 'timestamp',
            MYSQLI_TYPE_DATE      => 'date',
            MYSQLI_TYPE_TIME      => 'time',
            MYSQLI_TYPE_DATETIME  => 'datetime',
            MYSQLI_TYPE_NEWDATE   => 'newdate',

            MYSQLI_TYPE_SET => 'set',

            MYSQLI_TYPE_VAR_STRING => 'var_string',
            MYSQLI_TYPE_STRING     => 'string',

            MYSQLI_TYPE_GEOMETRY    => 'geometry',
            MYSQLI_TYPE_TINY_BLOB   => 'tiny_blob',
            MYSQLI_TYPE_MEDIUM_BLOB => 'medium_blob',
            MYSQLI_TYPE_LONG_BLOB   => 'long_blob',
            MYSQLI_TYPE_BLOB        => 'blob',
        ];

        $retVal    = [];
        $fieldData = $this->resultID->fetch_fields();

        foreach ($fieldData as $i => $data) {
            $retVal[$i]              = new stdClass();
            $retVal[$i]->name        = $data->name;
            $retVal[$i]->type        = $data->type;
            $retVal[$i]->type_name   = in_array($data->type, [1, 247], true) ? 'char' : ($dataTypes[$data->type] ?? null);
            $retVal[$i]->max_length  = $data->max_length;
            $retVal[$i]->primary_key = $data->flags & 2;
            $retVal[$i]->length      = $data->length;
            $retVal[$i]->default     = $data->def;
        }

        return $retVal;
    }

    /**
     * Frees the current result.
     */
    public function freeResult()
    {
        if (is_object($this->resultID)) {
            $this->resultID->free();
            $this->resultID = false;
        }
    }

    /**
     * Moves the internal pointer to the desired offset. This is called
     * internally before fetching results to make sure the result set
     * starts at zero.
     *
     * @return mixed
     */
    public function dataSeek(int $n = 0)
    {
        return $this->resultID->data_seek($n);
    }

    /**
     * Returns the result set as an array.
     *
     * Overridden by driver classes.
     *
     * @return mixed
     */
    protected function fetchAssoc()
    {
        return $this->resultID->fetch_assoc();
    }

    /**
     * Returns the result set as an object.
     *
     * Overridden by child classes.
     *
     * @return bool|Entity|object
     */
    protected function fetchObject(string $className = 'stdClass')
    {
        if (is_subclass_of($className, Entity::class)) {
            return empty($data = $this->fetchAssoc()) ? false : (new $className())->setAttributes($data);
        }

        return $this->resultID->fetch_object($className);
    }

    /**
     * Returns the number of rows in the resultID (i.e., mysqli_result object)
     */
    public function getNumRows(): int
    {
        if (! is_int($this->numRows)) {
            $this->numRows = $this->resultID->num_rows;
        }

        return $this->numRows;
    }
}
