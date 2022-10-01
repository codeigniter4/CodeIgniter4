<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\View;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ControlledCellTest extends CIUnitTestCase
{
    public function testCellRendersDefaultValues()
    {
        $result = view_cell('Tests\Support\View\Cells\GreetingCell');

        $this->assertStringContainsString('Hello World', $result);
    }

    public function testCellWithNamedView()
    {
        $result = view_cell('Tests\Support\View\Cells\SimpleNotice');

        $this->assertStringContainsString('4, 8, 15, 16, 23, 42', $result);
    }

    public function testCellThroughRenderMethod()
    {
        $result = view_cell('Tests\Support\View\Cells\RenderedNotice');

        $this->assertStringContainsString('4, 8, 15, 16, 23, 42', $result);
    }

    public function testCellWithComputedProperties()
    {
        $result = view_cell('Tests\Support\View\Cells\ListerCell', ['items' => ['one', 'two', 'three']]);

        $this->assertStringContainsString('-one -two -three', $result);
    }

    public function testCellWithPublicMethods()
    {
        $result = view_cell('Tests\Support\View\Cells\ColorsCell', ['color' => 'red']);

        $this->assertStringContainsString('warm', $result);

        $result = view_cell('Tests\Support\View\Cells\ColorsCell', ['color' => 'purple']);

        $this->assertStringContainsString('cool', $result);
    }

    public function testMountingDefaultValues()
    {
        $result = view_cell('Tests\Support\View\Cells\MultiplierCell');

        $this->assertStringContainsString('4', $result);
    }

    public function testMountingCustomValues()
    {
        $result = view_cell('Tests\Support\View\Cells\MultiplierCell', ['value' => 3, 'multiplier' => 3]);

        $this->assertStringContainsString('9', $result);
    }

    public function testMountValuesDefault()
    {
        $result = view_cell('Tests\Support\View\Cells\AdditionCell');

        $this->assertStringContainsString('2', (string)$result);
    }

    public function testMountValuesWithParams()
    {
        $result = view_cell('Tests\Support\View\Cells\AdditionCell', ['value' => 3]);

        $this->assertStringContainsString('3', (string)$result);
    }

    public function testMountValuesWithParamsAndMountParams()
    {
        $result = view_cell('Tests\Support\View\Cells\AdditionCell', ['value' => 3, 'number' => 4, 'skipAddition' => false]);

        $this->assertStringContainsString('7', (string)$result);

        $result = view_cell('Tests\Support\View\Cells\AdditionCell', ['value' => 3, 'number' => 4, 'skipAddition' => true]);

        $this->assertStringContainsString('3', (string)$result);
    }
}
