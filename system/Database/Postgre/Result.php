<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Postgre;

use CodeIgniter\Database\BaseResult;
use CodeIgniter\Entity\Entity;
use stdClass;

/**
 * Result for Postgre
 */
class Result extends BaseResult
{
    /**
     * Gets the number of fields in the result set.
     *
     * @return int
     */
    public function getFieldCount(): int
    {
        return pg_num_fields($this->resultID);
    }

    //--------------------------------------------------------------------

    /**
     * Generates an array of column names in the result set.
     *
     * @return array
     */
    public function getFieldNames(): array
    {
        $fieldNames = [];

        for ($i = 0, $c = $this->getFieldCount(); $i < $c; $i++) {
            $fieldNames[] = pg_field_name($this->resultID, $i);
        }

        return $fieldNames;
    }

    //--------------------------------------------------------------------

    /**
     * Generates an array of objects representing field meta-data.
     *
     * @return array
     */
    public function getFieldData(): array
    {
        $retVal = [];

        for ($i = 0, $c = $this->getFieldCount(); $i < $c; $i++) {
            $retVal[$i]             = new stdClass();
            $retVal[$i]->name       = pg_field_name($this->resultID, $i);
            $retVal[$i]->type       = pg_field_type_oid($this->resultID, $i);
            $retVal[$i]->type_name  = pg_field_type($this->resultID, $i);
            $retVal[$i]->max_length = pg_field_size($this->resultID, $i);
            $retVal[$i]->length     = $retVal[$i]->max_length;
            // $retVal[$i]->primary_key = (int)($fieldData[$i]->flags & 2);
            // $retVal[$i]->default     = $fieldData[$i]->def;
        }

        return $retVal;
    }

    //--------------------------------------------------------------------

    /**
     * Frees the current result.
     *
     * @return void
     */
    public function freeResult()
    {
        if (is_resource($this->resultID)) {
            pg_free_result($this->resultID);
            $this->resultID = false;
        }
    }

    //--------------------------------------------------------------------

    /**
     * Moves the internal pointer to the desired offset. This is called
     * internally before fetching results to make sure the result set
     * starts at zero.
     *
     * @param int $n
     *
     * @return mixed
     */
    public function dataSeek(int $n = 0)
    {
        return pg_result_seek($this->resultID, $n);
    }

    //--------------------------------------------------------------------

    /**
     * Returns the result set as an array.
     *
     * Overridden by driver classes.
     *
     * @return mixed
     */
    protected function fetchAssoc()
    {
        return pg_fetch_assoc($this->resultID);
    }

    //--------------------------------------------------------------------

    /**
     * Returns the result set as an object.
     *
     * Overridden by child classes.
     *
     * @param string $className
     *
     * @return bool|Entity|object
     */
    protected function fetchObject(string $className = 'stdClass')
    {
        if (is_subclass_of($className, Entity::class)) {
            return empty($data = $this->fetchAssoc()) ? false : (new $className())->setAttributes($data);
        }

        return pg_fetch_object($this->resultID, null, $className);
    }

    //--------------------------------------------------------------------

    /**
     * Returns the number of rows in the resultID (i.e., PostgreSQL query result resource)
     *
     * @return int The number of rows in the query result
     */
    public function getNumRows(): int
    {
        if (! is_int($this->numRows)) {
            $this->numRows = pg_num_rows($this->resultID);
        }

        return $this->numRows;
    }

    //--------------------------------------------------------------------
}
