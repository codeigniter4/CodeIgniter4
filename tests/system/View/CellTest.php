<?php

use CodeIgniter\View\Cell;
use CodeIgniter\Cache\Handlers\MockHandler;

include_once __DIR__ .'/SampleClass.php';

class CellTest extends \CIUnitTestCase
{
	protected $cache;

	/**
	 * @var Cell
	 */
	protected $cell;

	//--------------------------------------------------------------------

	public function setup()
	{
		$this->cache = new MockHandler();
	    $this->cell = new Cell($this->cache);
	}

	//--------------------------------------------------------------------

	public function testPrepareParamsReturnsNullWithInvalidParam()
	{
	    $this->assertTrue(is_null($this->cell->prepareParams(1.023)));
	}

	//--------------------------------------------------------------------

	public function testPrepareParamsReturnsNullWithEmptyString()
	{
	    $this->assertNull($this->cell->prepareParams(''));
	}

	//--------------------------------------------------------------------

	public function testPrepareParamsRetunsSelfWhenArray()
	{
	    $object = ['one' => 'two', 'three' => 'four'];

		$this->assertEquals($object, $this->cell->prepareParams($object));
	}

	//--------------------------------------------------------------------

	public function testPrepareParamsReturnsNullWithEmptyArray()
	{
	    $this->assertNull($this->cell->prepareParams([]));
	}

	//--------------------------------------------------------------------

	public function testPrepareParamsReturnsArrayWithString()
	{
	    $params = 'one=two three=four';
		$expected = ['one' => 'two', 'three' => 'four'];

		$this->assertEquals($expected, $this->cell->prepareParams($params));
	}

	//--------------------------------------------------------------------

	public function testPrepareParamsHandlesCommas()
	{
		$params = 'one=2, three=4.15';
		$expected = ['one' => 2, 'three' => 4.15];

		$this->assertEquals($expected, $this->cell->prepareParams($params));
	}

	//--------------------------------------------------------------------

	public function testPrepareParamsWorksWithoutSpaces()
	{
		$params = 'one=two,three=four';
		$expected = ['one' => 'two', 'three' => 'four'];

		$this->assertEquals($expected, $this->cell->prepareParams($params));
	}

	//--------------------------------------------------------------------

	public function testPrepareParamsWorksWithOddEqualsSpaces()
	{
		$params = 'one= two,three =four, five = six';
		$expected = ['one' => 'two', 'three' => 'four', 'five' => 'six'];

		$this->assertEquals($expected, $this->cell->prepareParams($params));
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Render
	//--------------------------------------------------------------------

	public function testDisplayRendersWithNamespacedClass()
	{
		$expected = 'Hello';

		$this->assertEquals($expected, $this->cell->render('\CodeIgniter\View\SampleClass::hello'));
	}

	//--------------------------------------------------------------------

	public function testDisplayRendersWithValidParamString()
	{
		$params = 'one=two,three=four';
		$expected = ['one' => 'two', 'three' => 'four'];

		$this->assertEquals(implode(',', $expected), $this->cell->render('\CodeIgniter\View\SampleClass::echobox', $params));
	}

	//--------------------------------------------------------------------

	public function testDisplayRendersWithStaticMethods()
	{
		$params = 'one=two,three=four';
		$expected = ['one' => 'two', 'three' => 'four'];

		$this->assertEquals(implode(',', $expected), $this->cell->render('\CodeIgniter\View\SampleClass::staticEcho', $params));
	}

	//--------------------------------------------------------------------
}
