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

namespace Tests\Support\Models;

use CodeIgniter\Database\BaseResult;
use stdClass;

/**
 * @internal
 *
 * @extends BaseResult<object|resource, object|resource>
 */
final class WithLockedRowResult extends BaseResult
{
    /**
     * @param list<array<string, mixed>> $rows
     * @param mixed                      $connID
     * @param mixed                      $resultID
     */
    public function __construct($connID, $resultID, private array $rows)
    {
        parent::__construct($connID, $resultID);
    }

    public function getFieldCount(): int
    {
        return 0;
    }

    /**
     * @return list<string>
     */
    public function getFieldNames(): array
    {
        return [];
    }

    /**
     * @return list<object>
     */
    public function getFieldData(): array
    {
        return [];
    }

    public function freeResult(): void
    {
    }

    public function dataSeek(int $n = 0): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>|false
     */
    protected function fetchAssoc(): array|false
    {
        return array_shift($this->rows) ?? false;
    }

    protected function fetchObject($className = stdClass::class): false|object
    {
        $row = $this->fetchAssoc();

        if ($row === false) {
            return false;
        }

        $object = new $className();

        foreach ($row as $key => $value) {
            $object->{$key} = $value;
        }

        return $object;
    }
}
