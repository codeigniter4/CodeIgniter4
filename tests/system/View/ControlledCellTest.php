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
use CodeIgniter\View\Exceptions\ViewException;
use LogicException;
use Tests\Support\View\Cells\AdditionCell;
use Tests\Support\View\Cells\AwesomeCell;
use Tests\Support\View\Cells\BadCell;
use Tests\Support\View\Cells\ColorsCell;
use Tests\Support\View\Cells\GreetingCell;
use Tests\Support\View\Cells\ListerCell;
use Tests\Support\View\Cells\MultiplierCell;
use Tests\Support\View\Cells\RenderedExtraDataNotice;
use Tests\Support\View\Cells\RenderedNotice;
use Tests\Support\View\Cells\SimpleNotice;

/**
 * @internal
 *
 * @group Others
 */
final class ControlledCellTest extends CIUnitTestCase
{
    public function testCellRendersDefaultValues(): void
    {
        $result = view_cell(GreetingCell::class);

        $this->assertStringContainsString('Hello World', $result);
    }

    public function testCellRendersViewWithActualClassName(): void
    {
        $result = view_cell(AwesomeCell::class);

        $this->assertStringContainsString('Found!', $result);
    }

    public function testCellWithNamedView(): void
    {
        $result = view_cell(SimpleNotice::class);

        $this->assertStringContainsString('4, 8, 15, 16, 23, 42', $result);
    }

    public function testCellThroughRenderMethod(): void
    {
        $result = view_cell(RenderedNotice::class);

        $this->assertStringContainsString('4, 8, 15, 16, 23, 42', $result);
    }

    public function testCellThroughRenderMethodWithExtraData(): void
    {
        $result = view_cell(RenderedExtraDataNotice::class);

        $this->assertStringContainsString('42, 23, 16, 15, 8, 4', $result);
    }

    public function testCellThrowsExceptionWhenCannotFindTheViewFile(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot locate the view file for the "Tests\\Support\\View\\Cells\\BadCell" cell.');

        view_cell(BadCell::class);
    }

    public function testCellWithParameters(): void
    {
        $result = view_cell(GreetingCell::class, 'greeting=Hi, name=CodeIgniter');

        $this->assertStringContainsString('Hi CodeIgniter', $result);

        // Should NOT be able to overwrite base class properties, like `view`.
        $result = view_cell(GreetingCell::class, 'greeting=Hi, name=CodeIgniter, view=foo');

        $this->assertStringContainsString('Hi CodeIgniter', $result);
    }

    public function testCellWithCustomMethod(): void
    {
        $result = view_cell('Tests\Support\View\Cells\GreetingCell::sayHello', 'greeting=Hi, name=CodeIgniter');

        $this->assertStringContainsString('Well, Hi CodeIgniter', $result);
    }

    public function testCellWithMissingCustomMethod(): void
    {
        $this->expectException(ViewException::class);
        $this->expectExceptionMessage(lang('View.invalidCellMethod', [
            'class'  => GreetingCell::class,
            'method' => 'sayGoodbye',
        ]));

        view_cell('Tests\Support\View\Cells\GreetingCell::sayGoodbye', 'greeting=Hi, name=CodeIgniter');
    }

    public function testCellWithComputedProperties(): void
    {
        $result = view_cell(ListerCell::class, ['items' => ['one', 'two', 'three']]);

        $this->assertStringContainsString('-one -two -three', $result);
    }

    public function testCellWithPublicMethods(): void
    {
        $result = view_cell(ColorsCell::class, ['color' => 'red']);

        $this->assertStringContainsString('warm', $result);

        $result = view_cell(ColorsCell::class, ['color' => 'purple']);

        $this->assertStringContainsString('cool', $result);
    }

    public function testMountingDefaultValues(): void
    {
        $result = view_cell(MultiplierCell::class);

        $this->assertStringContainsString('4', $result);
    }

    public function testMountingCustomValues(): void
    {
        $result = view_cell(MultiplierCell::class, ['value' => 3, 'multiplier' => 3]);

        $this->assertStringContainsString('9', $result);
    }

    public function testMountValuesDefault(): void
    {
        $result = view_cell(AdditionCell::class);

        $this->assertStringContainsString('2', $result);
    }

    public function testMountValuesWithParams(): void
    {
        $result = view_cell(AdditionCell::class, ['value' => 3]);

        $this->assertStringContainsString('3', $result);
    }

    public function testMountValuesWithParamsAndMountParams(): void
    {
        $result = view_cell(AdditionCell::class, ['value' => 3, 'number' => 4, 'skipAddition' => false]);

        $this->assertStringContainsString('7', $result);

        $result = view_cell(AdditionCell::class, ['value' => 3, 'number' => 4, 'skipAddition' => true]);

        $this->assertStringContainsString('3', $result);
    }

    public function testMountWithMissingParams(): void
    {
        // Don't provide any params
        $result = view_cell(AdditionCell::class, ['value' => 3]);

        $this->assertStringContainsString('3', $result);

        // Skip a parameter in the mount param list
        $result = view_cell(AdditionCell::class, ['value' => 3, $skipAddition = true]);

        $this->assertStringContainsString('3', $result);
    }
}
