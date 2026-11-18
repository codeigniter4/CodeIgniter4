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
use Override;
use stdClass;

/**
 * @extends BaseResult<object|resource, object|resource>
 */
class MockResult extends BaseResult
{
    #[Override]
    public function getFieldCount(): int
    {
        return 0;
    }

    /**
     * @return array{}
     */
    #[Override]
    public function getFieldNames(): array
    {
        return [];
    }

    /**
     * @return array{}
     */
    #[Override]
    public function getFieldData(): array
    {
        return [];
    }

    #[Override]
    public function freeResult()
    {
    }

    /**
     * @return true
     */
    #[Override]
    public function dataSeek(int $n = 0)
    {
        return true;
    }

    /**
     * @return array{}
     */
    #[Override]
    protected function fetchAssoc()
    {
        return [];
    }

    /**
     * @return object
     */
    #[Override]
    protected function fetchObject(string $className = stdClass::class)
    {
        return new $className();
    }

    #[Override]
    public function getNumRows(): int
    {
        return 0;
    }
}
