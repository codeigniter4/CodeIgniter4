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

use CodeIgniter\Database\BaseResult;
use CodeIgniter\Entity\Entity;
use stdClass;

/**
 * Result for OCI8
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
        return oci_num_fields($this->resultID);
    }

    /**
     * Generates an array of column names in the result set.
     */
    public function getFieldNames(): array
    {
        return array_map(fn ($fieldIndex) => oci_field_name($this->resultID, $fieldIndex), range(1, $this->getFieldCount()));
    }

    /**
     * Generates an array of objects representing field meta-data.
     */
    public function getFieldData(): array
    {
        return array_map(fn ($fieldIndex) => (object) [
            'name'       => oci_field_name($this->resultID, $fieldIndex),
            'type'       => oci_field_type($this->resultID, $fieldIndex),
            'max_length' => oci_field_size($this->resultID, $fieldIndex),
        ], range(1, $this->getFieldCount()));
    }

    /**
     * Frees the current result.
     *
     * @return void
     */
    public function freeResult()
    {
        if (is_resource($this->resultID)) {
            oci_free_statement($this->resultID);
            $this->resultID = false;
        }
    }

    /**
     * Moves the internal pointer to the desired offset. This is called
     * internally before fetching results to make sure the result set
     * starts at zero.
     *
     * @return false
     */
    public function dataSeek(int $n = 0)
    {
        // We can't support data seek by oci
        return false;
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
        return oci_fetch_assoc($this->resultID);
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
        $row = oci_fetch_object($this->resultID);

        if ($className === 'stdClass' || ! $row) {
            return $row;
        }
        if (is_subclass_of($className, Entity::class)) {
            return (new $className())->injectRawData((array) $row);
        }

        $instance = new $className();

        foreach (get_object_vars($row) as $key => $value) {
            $instance->{$key} = $value;
        }

        return $instance;
    }
}
