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
    public function getFieldCount(): int
    {
        return 0;
    }

    /**
     * @return array{}
     */
    public function getFieldNames(): array
    {
        return [];
    }

    /**
     * @return array{}
     */
    public function getFieldData(): array
    {
        return [];
    }

    public function freeResult()
    {
    }

    /**
     * @return true
     */
    public function dataSeek(int $n = 0)
    {
        return true;
    }

    /**
     * @return array{}
     */
    protected function fetchAssoc()
    {
        return [];
    }

    /**
     * @return object
     */
    protected function fetchObject(string $className = stdClass::class)
    {
        return new $className();
    }

    public function getNumRows(): int
    {
        return 0;
    }
}
