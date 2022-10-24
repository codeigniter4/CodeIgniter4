<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\SQLSRV;

use CodeIgniter\Database\BaseResult;
use CodeIgniter\Entity\Entity;
use stdClass;

/**
 * Result for SQLSRV
 *
 * @extends BaseResult<resource, resource>
 */
class Result extends BaseResult
{
    /**
     * Gets the number of fields in the result set.
     */
    public function getFieldCount(): int
    {
        return @sqlsrv_num_fields($this->resultID);
    }

    /**
     * Generates an array of column names in the result set.
     */
    public function getFieldNames(): array
    {
        $fieldNames = [];

        foreach (sqlsrv_field_metadata($this->resultID) as $field) {
            $fieldNames[] = $field['Name'];
        }

        return $fieldNames;
    }

    /**
     * Generates an array of objects representing field meta-data.
     */
    public function getFieldData(): array
    {
        static $dataTypes = [
            SQLSRV_SQLTYPE_BIGINT => 'bigint',
            SQLSRV_SQLTYPE_BIT    => 'bit',
            SQLSRV_SQLTYPE_CHAR   => 'char',

            SQLSRV_SQLTYPE_DATE           => 'date',
            SQLSRV_SQLTYPE_DATETIME       => 'datetime',
            SQLSRV_SQLTYPE_DATETIME2      => 'datetime2',
            SQLSRV_SQLTYPE_DATETIMEOFFSET => 'datetimeoffset',

            SQLSRV_SQLTYPE_DECIMAL => 'decimal',
            SQLSRV_SQLTYPE_FLOAT   => 'float',

            SQLSRV_SQLTYPE_IMAGE   => 'image',
            SQLSRV_SQLTYPE_INT     => 'int',
            SQLSRV_SQLTYPE_MONEY   => 'money',
            SQLSRV_SQLTYPE_NCHAR   => 'nchar',
            SQLSRV_SQLTYPE_NUMERIC => 'numeric',

            SQLSRV_SQLTYPE_NVARCHAR => 'nvarchar',
            SQLSRV_SQLTYPE_NTEXT    => 'ntext',

            SQLSRV_SQLTYPE_REAL          => 'real',
            SQLSRV_SQLTYPE_SMALLDATETIME => 'smalldatetime',
            SQLSRV_SQLTYPE_SMALLINT      => 'smallint',
            SQLSRV_SQLTYPE_SMALLMONEY    => 'smallmoney',
            SQLSRV_SQLTYPE_TEXT          => 'text',

            SQLSRV_SQLTYPE_TIME             => 'time',
            SQLSRV_SQLTYPE_TIMESTAMP        => 'timestamp',
            SQLSRV_SQLTYPE_TINYINT          => 'tinyint',
            SQLSRV_SQLTYPE_UNIQUEIDENTIFIER => 'uniqueidentifier',
            SQLSRV_SQLTYPE_UDT              => 'udt',
            SQLSRV_SQLTYPE_VARBINARY        => 'varbinary',
            SQLSRV_SQLTYPE_VARCHAR          => 'varchar',
            SQLSRV_SQLTYPE_XML              => 'xml',
        ];

        $retVal = [];

        foreach (sqlsrv_field_metadata($this->resultID) as $i => $field) {
            $retVal[$i] = new stdClass();

            $retVal[$i]->name       = $field['Name'];
            $retVal[$i]->type       = $field['Type'];
            $retVal[$i]->type_name  = $dataTypes[$field['Type']] ?? null;
            $retVal[$i]->max_length = $field['Size'];
        }

        return $retVal;
    }

    /**
     * Frees the current result.
     */
    public function freeResult()
    {
        if (is_resource($this->resultID)) {
            sqlsrv_free_stmt($this->resultID);
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
        if ($n > 0) {
            for ($i = 0; $i < $n; $i++) {
                if (sqlsrv_fetch($this->resultID) === false) {
                    return false;
                }
            }
        }

        return true;
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
        return sqlsrv_fetch_array($this->resultID, SQLSRV_FETCH_ASSOC);
    }

    /**
     * Returns the result set as an object.
     *
     * @return bool|Entity|object
     */
    protected function fetchObject(string $className = 'stdClass')
    {
        if (is_subclass_of($className, Entity::class)) {
            return empty($data = $this->fetchAssoc()) ? false : (new $className())->setAttributes($data);
        }

        return sqlsrv_fetch_object($this->resultID, $className);
    }

    /**
     * Returns the number of rows in the resultID (i.e., SQLSRV query result resource)
     */
    public function getNumRows(): int
    {
        if (! is_int($this->numRows)) {
            $this->numRows = sqlsrv_num_rows($this->resultID);
        }

        return $this->numRows;
    }
}
