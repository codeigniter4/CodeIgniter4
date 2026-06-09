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

namespace CodeIgniter\Database;

use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;
use stdClass;

/**
 * @internal
 */
#[Group('Others')]
final class BaseResultTest extends CIUnitTestCase
{
    /**
     * Create a minimal concrete implementation of BaseResult for testing.
     *
     * @param list<array<string,mixed>> $resultArray  Result set as arrays.
     * @param list<object>              $resultObject Result set as objects.
     *
     * @return BaseResult<mixed, mixed>
     */
    private function createResultDouble(array $resultArray, array $resultObject): BaseResult
    {
        return new
        /**
         * @extends BaseResult<mixed, mixed>
         */
        class ($resultArray, $resultObject) extends BaseResult {
            /**
             * @param list<array<string,mixed>> $resultArray  Result set as arrays.
             * @param list<object>              $resultObject Result set as objects.
             */
            public function __construct(array $resultArray, array $resultObject)
            {
                $this->resultArray  = $resultArray;
                $this->resultObject = $resultObject;
                $this->currentRow   = 0;

                $connId   = null;
                $resultId = null;
                parent::__construct($connId, $resultId);
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
             * @return false|list<array<string,mixed>>|null
             */
            protected function fetchAssoc(): array|bool|null
            {
                return false;
            }

            protected function fetchObject(string $className = stdClass::class)
            {
                return false;
            }
        };
    }

    // --------------------------------------------------------------------
    // getRowArray()
    // --------------------------------------------------------------------

    public function testGetRowArrayReturnsRow(): void
    {
        $result = $this->createResultDouble(
            [
                ['id' => 1, 'name' => 'John'],
                ['id' => 2, 'name' => 'Jane'],
            ],
            [],
        );

        $this->assertSame(['id' => 1, 'name' => 'John'], $result->getRowArray(0));
        $this->assertSame(['id' => 2, 'name' => 'Jane'], $result->getRowArray(1));
    }

    public function testGetRowArrayReturnsNullForEmptyResult(): void
    {
        $result = $this->createResultDouble([], []);

        $this->assertNull($result->getRowArray(0));
    }

    public function testGetRowArrayReturnsFirstRowByDefault(): void
    {
        $result = $this->createResultDouble(
            [
                ['id' => 1, 'name' => 'John'],
                ['id' => 2, 'name' => 'Jane'],
            ],
            [],
        );

        $this->assertSame(['id' => 1, 'name' => 'John'], $result->getRowArray());
    }

    // --------------------------------------------------------------------
    // getRowObject()
    // --------------------------------------------------------------------

    public function testGetRowObjectReturnsObject(): void
    {
        $row1       = new stdClass();
        $row1->id   = 1;
        $row1->name = 'John';
        $row2       = new stdClass();
        $row2->id   = 2;
        $row2->name = 'Jane';

        $result = $this->createResultDouble([], [$row1, $row2]);

        $this->assertSame($row1, $result->getRowObject(0));
        $this->assertSame($row2, $result->getRowObject(1));
    }

    public function testGetRowObjectReturnsNullForEmptyResult(): void
    {
        $result = $this->createResultDouble([], []);

        $this->assertNull($result->getRowObject(0));
    }

    public function testGetRowObjectReturnsFirstRowByDefault(): void
    {
        $row1       = new stdClass();
        $row1->id   = 1;
        $row1->name = 'John';

        $result = $this->createResultDouble([], [$row1]);

        $this->assertSame($row1, $result->getRowObject());
    }

    public function testGetRowObjectAndGetRowArrayShareCurrentRow(): void
    {
        $row1       = new stdClass();
        $row1->id   = 1;
        $row1->name = 'John';
        $row2       = new stdClass();
        $row2->id   = 2;
        $row2->name = 'Jane';

        $result = $this->createResultDouble(
            [
                ['id' => 1, 'name' => 'John'],
                ['id' => 2, 'name' => 'Jane'],
            ],
            [$row1, $row2],
        );

        // getRowObject(1) should advance currentRow to 1 (same as getRowArray would)
        $result->getRowObject(1);
        $this->assertSame(['id' => 2, 'name' => 'Jane'], $result->getRowArray(1));
    }

    public function testGetRowObjectUsesCurrentRowLikeGetRowArray(): void
    {
        $row1       = new stdClass();
        $row1->id   = 1;
        $row1->name = 'John';
        $row2       = new stdClass();
        $row2->id   = 2;
        $row2->name = 'Jane';

        $result = $this->createResultDouble(
            [
                ['id' => 1, 'name' => 'John'],
                ['id' => 2, 'name' => 'Jane'],
            ],
            [$row1, $row2],
        );

        // Both methods should advance currentRow consistently
        $result->getRowObject(1);
        $result->getRowArray();
        $this->assertSame($row1, $result->getRowObject());
    }

    // --------------------------------------------------------------------
    // getRow() — convenience wrapper
    // --------------------------------------------------------------------

    public function testGetRowWithInvalidIndexReturnsFirstRow(): void
    {
        $result = $this->createResultDouble(
            [['id' => 1, 'name' => 'John']],
            [],
        );

        $this->assertSame(['id' => 1, 'name' => 'John'], $result->getRow(999, 'array'));
    }

    public function testGetRowObjectWithInvalidIndexReturnsFirstRow(): void
    {
        $row1       = new stdClass();
        $row1->id   = 1;
        $row1->name = 'John';

        $result = $this->createResultDouble([], [$row1]);

        $this->assertSame($row1, $result->getRow(999, 'object'));
    }

    public function testGetRowNullForColumnNameNotFound(): void
    {
        $result = $this->createResultDouble(
            [['id' => 1, 'name' => 'John']],
            [],
        );

        $this->assertNull($result->getRow('nonexistent', 'array'));
    }

    // --------------------------------------------------------------------
    // Custom Result Object
    // --------------------------------------------------------------------

    public function testGetCustomRowObjectWithInvalidIndexReturnsFirstRow(): void
    {
        $row       = new stdClass();
        $row->id   = 1;
        $row->name = 'John';

        $result                                      = $this->createResultDouble([], []);
        $result->customResultObject[stdClass::class] = [$row];

        $this->assertSame($row, $result->getCustomRowObject(999, stdClass::class));
    }

    // --------------------------------------------------------------------
    // Fallback Tests (Null return on invalid currentRow)
    // --------------------------------------------------------------------

    public function testGetRowArrayReturnsNullWhenCurrentRowIsInvalid(): void
    {
        $result = $this->createResultDouble(
            [['id' => 1, 'name' => 'John']],
            [],
        );

        $result->currentRow = 999;

        $this->assertNull($result->getRowArray(999));
    }

    public function testGetRowObjectReturnsNullWhenCurrentRowIsInvalid(): void
    {
        $row1       = new stdClass();
        $row1->id   = 1;
        $row1->name = 'John';

        $result = $this->createResultDouble(
            [],
            [$row1],
        );

        $result->currentRow = 999;

        $this->assertNull($result->getRowObject(999));
    }

    public function testGetCustomRowObjectReturnsNullWhenCurrentRowIsInvalid(): void
    {
        $row1       = new stdClass();
        $row1->id   = 1;
        $row1->name = 'John';

        $result                                      = $this->createResultDouble([], []);
        $result->customResultObject[stdClass::class] = [$row1];

        $result->currentRow = 999;

        $this->assertNotInstanceOf(stdClass::class, $result->getCustomRowObject(999, stdClass::class));
    }

    public function testGetPreviousRowReturnsNullWhenCurrentRowIsInvalid(): void
    {
        $result = $this->createResultDouble(
            [
                ['id' => 1],
                ['id' => 2],
            ],
            [],
        );

        $result->currentRow = -1;

        $this->assertNull($result->getPreviousRow('array'));
    }
}
