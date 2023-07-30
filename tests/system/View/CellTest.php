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

use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCache;
use CodeIgniter\View\Exceptions\ViewException;

/**
 * @internal
 *
 * @group Others
 */
final class CellTest extends CIUnitTestCase
{
    private MockCache $cache;
    private Cell $cell;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = new MockCache();
        $this->cell  = new Cell($this->cache);
    }

    public function testPrepareParamsReturnsEmptyArrayWithInvalidParam(): void
    {
        $this->assertSame([], $this->cell->prepareParams(1.023));
    }

    public function testPrepareParamsReturnsNullWithEmptyString(): void
    {
        $this->assertSame([], $this->cell->prepareParams(''));
    }

    public function testPrepareParamsReturnsSelfWhenArray(): void
    {
        $object = [
            'one'   => 'two',
            'three' => 'four',
        ];

        $this->assertSame($object, $this->cell->prepareParams($object));
    }

    public function testPrepareParamsReturnsEmptyArrayWithEmptyArray(): void
    {
        $this->assertSame([], $this->cell->prepareParams([]));
    }

    public function testPrepareParamsReturnsArrayWithString(): void
    {
        $params   = 'one=two three=four';
        $expected = [
            'one'   => 'two',
            'three' => 'four',
        ];

        $this->assertSame($expected, $this->cell->prepareParams($params));
    }

    public function testPrepareParamsHandlesCommas(): void
    {
        $params   = 'one=2, three=4.15';
        $expected = [
            'one'   => '2',
            'three' => '4.15',
        ];

        $this->assertSame($expected, $this->cell->prepareParams($params));
    }

    public function testPrepareParamsWorksWithoutSpaces(): void
    {
        $params   = 'one=two,three=four';
        $expected = [
            'one'   => 'two',
            'three' => 'four',
        ];

        $this->assertSame($expected, $this->cell->prepareParams($params));
    }

    public function testPrepareParamsWorksWithOddEqualsSpaces(): void
    {
        $params   = 'one= two,three =four, five = six';
        $expected = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $this->assertSame($expected, $this->cell->prepareParams($params));
    }

    // Render

    public function testDisplayRendersWithNamespacedClass(): void
    {
        $expected = 'Hello';

        $this->assertSame($expected, $this->cell->render('\Tests\Support\View\SampleClass::hello'));
    }

    public function testDisplayRendersTwoCellsWithSameShortName(): void
    {
        $output = $this->cell->render('\Tests\Support\View\SampleClass::hello');

        $this->assertSame('Hello', $output);

        $output = $this->cell->render('\Tests\Support\View\OtherCells\SampleClass::hello');

        $this->assertSame('Good-bye!', $output);
    }

    public function testDisplayRendersWithValidParamString(): void
    {
        $params   = 'one=two,three=four';
        $expected = [
            'one'   => 'two',
            'three' => 'four',
        ];

        $this->assertSame(implode(',', $expected), $this->cell->render('\Tests\Support\View\SampleClass::echobox', $params));
    }

    public function testDisplayRendersWithStaticMethods(): void
    {
        $params   = 'one=two,three=four';
        $expected = [
            'one'   => 'two',
            'three' => 'four',
        ];

        $this->assertSame(implode(',', $expected), $this->cell->render('\Tests\Support\View\SampleClass::staticEcho', $params));
    }

    public function testOptionsEmptyArray(): void
    {
        $params   = [];
        $expected = [];

        $this->assertSame(implode(',', $expected), $this->cell->render('\Tests\Support\View\SampleClass::staticEcho', $params));
    }

    public function testOptionsNoParams(): void
    {
        $expected = [];

        $this->assertSame(implode(',', $expected), $this->cell->render('\Tests\Support\View\SampleClass::staticEcho'));
    }

    public function testCellEmptyParams(): void
    {
        $params   = ',';
        $expected = 'Hello World';

        $this->assertSame($expected, $this->cell->render('\Tests\Support\View\SampleClass::index', $params));
    }

    public function testCellClassMissing(): void
    {
        $this->expectException(ViewException::class);
        $params   = 'one=two,three=four';
        $expected = [
            'one'   => 'two',
            'three' => 'four',
        ];

        $this->assertSame(implode(',', $expected), $this->cell->render('::echobox', $params));
    }

    public function testCellMethodMissing(): void
    {
        $this->expectException(ViewException::class);
        $params   = 'one=two,three=four';
        $expected = [
            'one'   => 'two',
            'three' => 'four',
        ];

        $this->assertSame(implode(',', $expected), $this->cell->render('\Tests\Support\View\SampleClass::', $params));
    }

    public function testCellBadClass(): void
    {
        $this->expectException(ViewException::class);
        $params   = 'one=two,three=four';
        $expected = 'Hello World';

        $this->assertSame($expected, $this->cell->render('\CodeIgniter\View\GoodQuestion::', $params));
    }

    public function testCellBadMethod(): void
    {
        $this->expectException(ViewException::class);
        $params   = 'one=two,three=four';
        $expected = 'Hello World';

        $this->assertSame($expected, $this->cell->render('\Tests\Support\View\SampleClass::notThere', $params));
    }

    public function testRenderCached(): void
    {
        $params   = 'one=two,three=four';
        $expected = [
            'one'   => 'two',
            'three' => 'four',
        ];

        $this->assertSame(implode(',', $expected), $this->cell->render('\Tests\Support\View\SampleClass::echobox', $params, 60, 'rememberme'));
        $params = 'one=six,three=five';
        $this->assertSame(implode(',', $expected), $this->cell->render('\Tests\Support\View\SampleClass::echobox', $params, 1, 'rememberme'));
    }

    public function testRenderCachedAutoName(): void
    {
        $params   = 'one=two,three=four';
        $expected = [
            'one'   => 'two',
            'three' => 'four',
        ];

        $this->assertSame(implode(',', $expected), $this->cell->render('\Tests\Support\View\SampleClass::echobox', $params, 60));
        $params = 'one=six,three=five';
        // When auto-generating it takes the params as part of cachename, so it wouldn't have actually cached this, but
        // we want to make sure it doesn't throw us a curveball here.
        $this->assertSame('six,five', $this->cell->render('\Tests\Support\View\SampleClass::echobox', $params, 1));
    }

    public function testParametersMatch(): void
    {
        $params = [
            'p1' => 'one',
            'p2' => 'two',
            'p4' => 'three',
        ];
        $expected = 'Right on';

        $this->assertSame($expected, $this->cell->render('\Tests\Support\View\SampleClass::work', $params));
    }

    public function testParametersDontMatch(): void
    {
        $this->expectException(ViewException::class);
        $params   = 'p1=one,p2=two,p3=three';
        $expected = 'Right on';

        $this->assertSame($expected, $this->cell->render('\Tests\Support\View\SampleClass::work', $params));
    }

    public function testCallInitControllerIfMethodExists(): void
    {
        $this->assertSame(
            Response::class,
            $this->cell->render('\Tests\Support\View\SampleClassWithInitController::index')
        );
    }

    public function testLocateCellSuccess(): void
    {
        $this->assertSame('Hello World!', $this->cell->render('StarterCell::hello'));
        $this->assertSame('Hello CodeIgniter!', $this->cell->render('StarterCell::hello', ['name' => 'CodeIgniter']));
    }
}
