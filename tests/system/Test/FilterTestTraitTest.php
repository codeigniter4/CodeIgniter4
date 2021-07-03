<?php

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

    public function testDidRunTraitSetUp()
    {
        $this->assertTrue($this->doneFilterSetUp);
        $this->assertInstanceOf(RequestInterface::class, $this->request);
    }

    public function testGetCallerReturnsClosure()
    {
        $caller = $this->getFilterCaller('test-customfilter', 'before');

        $this->assertIsCallable($caller);
        $this->assertInstanceOf('Closure', $caller);
    }

    public function testGetCallerInvalidPosition()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid filter position passed: banana');

        $this->getFilterCaller('test-customfilter', 'banana');
    }

    public function testCallerUsesClonedInstance()
    {
        $caller = $this->getFilterCaller('test-customfilter', 'before');
        $result = $caller();

        $this->assertObjectNotHasAttribute('url', $this->request);

        $this->assertObjectHasAttribute('url', $result);
        $this->assertSame('http://hellowworld.com', $result->url);
    }

    public function testGetFiltersForRoute()
    {
        $result = $this->getFiltersForRoute('/', 'before');

        $this->assertSame(['test-customfilter'], $result);
    }

    public function testAssertFilter()
    {
        $this->assertFilter('/', 'before', 'test-customfilter');
        $this->assertFilter('/', 'after', 'toolbar');
    }

    public function testAssertNotFilter()
    {
        $this->assertNotFilter('/', 'before', 'foobar');
        $this->assertNotFilter('/', 'after', 'test-customfilter');
    }

    public function testAssertHasFilters()
    {
        $this->assertHasFilters('/', 'before');
    }

    public function testAssertNotHasFilters()
    {
        $this->filtersConfig->globals['before'] = [];

        $this->assertNotHasFilters('/', 'before');
    }
}
