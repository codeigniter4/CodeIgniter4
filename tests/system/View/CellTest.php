<?php

use CodeIgniter\View\Cell;
use CodeIgniter\View\Exceptions\ViewException;
use CodeIgniter\Test\Mock\MockCache;

class CellTest extends \CodeIgniter\Test\CIUnitTestCase
{

	protected $cache;

	/**
	 * @var Cell
	 */
	protected $cell;

	//--------------------------------------------------------------------

	protected function setUp(): void
	{
		parent::setUp();

		$this->cache = new MockCache();
		$this->cell  = new Cell($this->cache);
	}

	//--------------------------------------------------------------------

	public function testPrepareParamsReturnsEmptyArrayWithInvalidParam()
	{
		$this->assertEquals([], $this->cell->prepareParams(1.023));
	}

	//--------------------------------------------------------------------

	public function testPrepareParamsReturnsNullWithEmptyString()
	{
		$this->assertEquals([], $this->cell->prepareParams(''));
	}

	//--------------------------------------------------------------------

	public function testPrepareParamsReturnsSelfWhenArray()
	{
		$object = [
			'one'   => 'two',
			'three' => 'four',
		];

		$this->assertEquals($object, $this->cell->prepareParams($object));
	}

	//--------------------------------------------------------------------

	public function testPrepareParamsReturnsEmptyArrayWithEmptyArray()
	{
		$this->assertEquals([], $this->cell->prepareParams([]));
	}

	//--------------------------------------------------------------------

	public function testPrepareParamsReturnsArrayWithString()
	{
		$params   = 'one=two three=four';
		$expected = [
			'one'   => 'two',
			'three' => 'four',
		];

		$this->assertEquals($expected, $this->cell->prepareParams($params));
	}

	//--------------------------------------------------------------------

	public function testPrepareParamsHandlesCommas()
	{
		$params   = 'one=2, three=4.15';
		$expected = [
			'one'   => 2,
			'three' => 4.15,
		];

		$this->assertEquals($expected, $this->cell->prepareParams($params));
	}

	//--------------------------------------------------------------------

	public function testPrepareParamsWorksWithoutSpaces()
	{
		$params   = 'one=two,three=four';
		$expected = [
			'one'   => 'two',
			'three' => 'four',
		];

		$this->assertEquals($expected, $this->cell->prepareParams($params));
	}

	//--------------------------------------------------------------------

	public function testPrepareParamsWorksWithOddEqualsSpaces()
	{
		$params   = 'one= two,three =four, five = six';
		$expected = [
			'one'   => 'two',
			'three' => 'four',
			'five'  => 'six',
		];

		$this->assertEquals($expected, $this->cell->prepareParams($params));
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Render
	//--------------------------------------------------------------------

	public function testDisplayRendersWithNamespacedClass()
	{
		$expected = 'Hello';

		$this->assertEquals($expected, $this->cell->render('\Tests\Support\View\SampleClass::hello'));
	}

	//--------------------------------------------------------------------

	public function testDisplayRendersWithValidParamString()
	{
		$params   = 'one=two,three=four';
		$expected = [
			'one'   => 'two',
			'three' => 'four',
		];

		$this->assertEquals(implode(',', $expected), $this->cell->render('\Tests\Support\View\SampleClass::echobox', $params));
	}

	//--------------------------------------------------------------------

	public function testDisplayRendersWithStaticMethods()
	{
		$params   = 'one=two,three=four';
		$expected = [
			'one'   => 'two',
			'three' => 'four',
		];

		$this->assertEquals(implode(',', $expected), $this->cell->render('\Tests\Support\View\SampleClass::staticEcho', $params));
	}

	//--------------------------------------------------------------------

	public function testOptionsEmptyArray()
	{
		$params   = [];
		$expected = [];

		$this->assertEquals(implode(',', $expected), $this->cell->render('\Tests\Support\View\SampleClass::staticEcho', $params));
	}

	public function testOptionsNoParams()
	{
		$expected = [];

		$this->assertEquals(implode(',', $expected), $this->cell->render('\Tests\Support\View\SampleClass::staticEcho'));
	}

	public function testCellEmptyParams()
	{
		$params   = ',';
		$expected = 'Hello World';

		$this->assertEquals($expected, $this->cell->render('\Tests\Support\View\SampleClass::index', $params));
	}

	//--------------------------------------------------------------------

	public function testCellClassMissing()
	{
		$this->expectException(ViewException::class);
		$params   = 'one=two,three=four';
		$expected = [
			'one'   => 'two',
			'three' => 'four',
		];

		$this->assertEquals(implode(',', $expected), $this->cell->render('::echobox', $params));
	}

	public function testCellMethodMissing()
	{
		$this->expectException(ViewException::class);
		$params   = 'one=two,three=four';
		$expected = [
			'one'   => 'two',
			'three' => 'four',
		];

		$this->assertEquals(implode(',', $expected), $this->cell->render('\Tests\Support\View\SampleClass::', $params));
	}

	public function testCellBadClass()
	{
		$this->expectException(ViewException::class);
		$params   = 'one=two,three=four';
		$expected = 'Hello World';

		$this->assertEquals($expected, $this->cell->render('\CodeIgniter\View\GoodQuestion::', $params));
	}

	public function testCellBadMethod()
	{
		$this->expectException(ViewException::class);
		$params   = 'one=two,three=four';
		$expected = 'Hello World';

		$this->assertEquals($expected, $this->cell->render('\Tests\Support\View\SampleClass::notThere', $params));
	}

	//--------------------------------------------------------------------

	public function testRenderCached()
	{
		$params   = 'one=two,three=four';
		$expected = [
			'one'   => 'two',
			'three' => 'four',
		];

		$this->assertEquals(implode(',', $expected), $this->cell->render('\Tests\Support\View\SampleClass::echobox', $params, 60, 'rememberme'));
		$params = 'one=six,three=five';
		$this->assertEquals(implode(',', $expected), $this->cell->render('\Tests\Support\View\SampleClass::echobox', $params, 1, 'rememberme'));
	}

	//--------------------------------------------------------------------

	public function testParametersMatch()
	{
		$params   = [
			'p1' => 'one',
			'p2' => 'two',
			'p4' => 'three',
		];
		$expected = 'Right on';

		$this->assertEquals($expected, $this->cell->render('\Tests\Support\View\SampleClass::work', $params));
	}

	public function testParametersDontMatch()
	{
		$this->expectException(ViewException::class);
		$params   = 'p1=one,p2=two,p3=three';
		$expected = 'Right on';

		$this->assertEquals($expected, $this->cell->render('\Tests\Support\View\SampleClass::work', $params));
	}

}
