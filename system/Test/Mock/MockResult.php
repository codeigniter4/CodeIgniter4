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

namespace CodeIgniter\Test\Mock;

use CodeIgniter\Database\BaseResult;
use stdClass;

/**
 * @extends BaseResult<object|resource, object|resource>
 */
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
     *
     * @return array{}
     */
    public function getFieldNames(): array
    {
        return [];
    }

    /**
     * Generates an array of objects representing field meta-data.
     *
     * @return array{}
     */
    public function getFieldData(): array
    {
        return [];
    }

    /**
     * Frees the current result.
     *
     * @return void
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
     * @return bool
     */
    public function dataSeek($n = 0)
    {
        return true;
    }

    /**
     * Returns the result set as an array.
     *
     * Overridden by driver classes.
     *
     * @return array{}
     */
    protected function fetchAssoc()
    {
        return [];
    }

    /**
     * Returns the result set as an object.
     *
     * @param class-string $className
     *
     * @return object
     */
    protected function fetchObject($className = stdClass::class)
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
