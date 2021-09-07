<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Mock;

use CodeIgniter\Database\BaseResult;

class MockResult extends BaseResult
{
    /**
     * Gets the number of fields in the result set.
     */
    public function getFieldCount(): int
    {
        return 0;
    }

    /**
     * Generates an array of column names in the result set.
     */
    public function getFieldNames(): array
    {
        return [];
    }

    /**
     * Generates an array of objects representing field meta-data.
     */
    public function getFieldData(): array
    {
        return [];
    }

    /**
     * Frees the current result.
     *
     * @return mixed
     */
    public function freeResult()
    {
    }

    /**
     * Moves the internal pointer to the desired offset. This is called
     * internally before fetching results to make sure the result set
     * starts at zero.
     *
     * @param int $n
     *
     * @return mixed
     */
    public function dataSeek($n = 0)
    {
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
    }

    /**
     * Returns the result set as an object.
     *
     * Overridden by child classes.
     *
     * @param string $className
     *
     * @return object
     */
    protected function fetchObject($className = 'stdClass')
    {
        return new $className();
    }

    /**
     * Gets the number of fields in the result set.
     */
    public function getNumRows(): int
    {
        return 0;
    }
}
