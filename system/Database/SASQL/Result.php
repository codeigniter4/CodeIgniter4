<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\SASQL;

use CodeIgniter\Database\BaseResult;
use CodeIgniter\Entity\Entity;
use stdClass;

/**
 * Result for SASQL
 */
class Result extends BaseResult
{
    /**
     * Gets the number of fields in the result set.
     */
    public function getFieldCount(): int
    {
        return @sasql_num_fields($this->resultID);
    }

    /**
     * Generates an array of column names in the result set.
     */
    public function getFieldNames(): array
    {
        $fieldNames = [];

        /*foreach (sasql_fetch_field($this->resultID) as $field) {
            $fieldNames[] = $field['Name'];
        }*/
        for($i = 0; $i < sasql_num_fields($this->resultID); $i++) {
            $fieldInfo      = sasql_fetch_field($this->resultID, $i);
            $fieldNames[]   = $fieldInfo->name;
        }

        return $fieldNames;
    }

    /**
     * Generates an array of objects representing field meta-data.
     */
    public function getFieldData(): array
    {
        /*
        bigint	    64 bits Huit octets
        int         32 bits Quatre octets
        smallint	16 bits Deux octets
        tinyint	    8 bits  Un octet
        */
        static $dataTypes = [
            DT_BIT          => 'tinyint',
            DT_SMALLINT     => 'smallint',
            DT_UNSSMALLINT  => 'smallint',
            DT_TINYINT      => 'tinyint',
            DT_BIGINT       => 'bigint',
            DT_UNSBIGINT    => 'bigint',
            DT_INT          => 'int',
            DT_UNSINT       => 'int',
            DT_FLOAT        => 'float',
            DT_DOUBLE       => 'float',
            DT_STRING       => 'text',
            DT_NSTRING      => 'ntext',
            DT_DATE         => 'date',
            DT_TIME         => 'datetime',
            DT_TIMESTAMP    => 'timestamp',
            DT_FIXCHAR      => 'char',
            DT_NFIXCHAR     => 'nchar',
            DT_VARCHAR      => 'varchar',
            DT_NVARCHAR     => 'nvarchar',
            DT_LONGVARCHAR  => 'varchar',
            DT_LONGNVARCHAR => 'nvarchar',
            DT_BINARY       => 'varbinary',
            DT_LONGBINARY   => 'varbinary'
        ];

        $retVal = [];

        foreach (sasql_fetch_field($this->resultID) as $i => $field) {
            $retVal[$i] = new stdClass();

            $retVal[$i]->id         = $field['id'];
            $retVal[$i]->name       = $field['name'];
            $retVal[$i]->numeric    = $field['numeric'];
            $retVal[$i]->type       = $field['type'];
            $retVal[$i]->type_name  = $dataTypes[$field['type']] ?? null;
            $retVal[$i]->max_length = $field['length'];
            $retVal[$i]->precision  = $field['precision'];
            $retVal[$i]->scale      = $field['scale'];
        }

        return $retVal;
    }

    /**
     * Frees the current result.
     */
    public function freeResult()
    {
        if (is_resource($this->resultID)) {
            sasql_free_result($this->resultID);
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
                if (sasql_data_seek($this->resultID, $i) === false) {
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
        return sasql_fetch_array($this->resultID, SASQL_ASSOC);
    }

    /**
     * Returns the result set as an object.
     *
     * @return bool|Entity|object
     */
    protected function fetchObject(string $className = 'stdClass')
    {
        return sasql_fetch_object($this->resultID);
    }

    /**
     * Returns the number of rows in the resultID (i.e., SASQL query result resource)
     */
    public function getNumRows(): int
    {
        if (! is_int($this->numRows)) {
            $this->numRows = sasql_num_rows($this->resultID);
        }

        return $this->numRows;
    }
}
