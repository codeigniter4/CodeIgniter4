<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use CodeIgniter\Filters\Filters;
use CodeIgniter\HTTP\RequestInterface;
use Tests\Support\Filters\Customfilter;

/**
 * FilterTestTrait Test
 *
 * Implements the FilterTestTrait and then runs tests
 * on itself. Most test rely on the test filter defined
 * in test/_support/Filters/Customfilter.php:
 *  - alias: test-customfilter
 *  - class: \Tests\Support\Filters\Customfilter::class
 *
 * @internal
 *
 * @group Others
 */
final class FilterTestTraitTest extends CIUnitTestCase
{
    use FilterTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        // Apply the Custom Filter
        $this->filtersConfig->aliases['test-customfilter'] = Customfilter::class;
        $this->filtersConfig->globals['before']            = ['test-customfilter'];
    }

    public function testDidRunTraitSetUp(): void
    {
        $this->assertTrue($this->doneFilterSetUp);
        $this->assertInstanceOf(RequestInterface::class, $this->request);
    }

    public function testGetCallerReturnsClosure(): void
    {
        $caller = $this->getFilterCaller('test-customfilter', 'before');

        $this->assertIsCallable($caller);
        $this->assertInstanceOf('Closure', $caller);
    }

    public function testGetCallerInvalidPosition(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid filter position passed: banana');

        $this->getFilterCaller('test-customfilter', 'banana');
    }

    public function testCallerUsesClonedInstance(): void
    {
        $caller = $this->getFilterCaller('test-customfilter', 'before');
        $result = $caller();

        $this->assertSame('http://hellowworld.com', $result->getBody());

        $this->resetServices();
    }

    public function testGetFiltersForRoute(): void
    {
        $result = $this->getFiltersForRoute('/', 'before');

        $this->assertSame(['test-customfilter'], $result);
    }

    public function testAssertFilter(): void
    {
        $this->assertFilter('/', 'before', 'test-customfilter');
        $this->assertFilter('/', 'after', 'toolbar');
    }

    public function testAssertNotFilter(): void
    {
        $this->assertNotFilter('/', 'before', 'foobar');
        $this->assertNotFilter('/', 'after', 'test-customfilter');
    }

    public function testAssertHasFilters(): void
    {
        $this->assertHasFilters('/', 'before');
    }

    public function testAssertNotHasFilters(): void
    {
        $this->filtersConfig->globals['before'] = [];

        $this->assertNotHasFilters('/', 'before');
    }
}
