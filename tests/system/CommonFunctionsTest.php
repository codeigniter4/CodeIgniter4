<?php 

class CommomFunctionsTest extends \CIUnitTestCase
{

	//--------------------------------------------------------------------

	public function setUp()
	{
	}

	//--------------------------------------------------------------------

	public function testStringifyAttributes()
	{
		$this->assertEquals(' class="foo" id="bar"', stringify_attributes(array('class' => 'foo', 'id' => 'bar')));

		$atts = new stdClass;
		$atts->class = 'foo';
		$atts->id = 'bar';
		$this->assertEquals(' class="foo" id="bar"', stringify_attributes($atts));

		$atts = new stdClass;
		$this->assertEquals('', stringify_attributes($atts));

		$this->assertEquals(' class="foo" id="bar"', stringify_attributes('class="foo" id="bar"'));

		$this->assertEquals('', stringify_attributes(array()));
	}

	// ------------------------------------------------------------------------

	public function testStringifyJsAttributes()
	{
		$this->assertEquals('width=800,height=600', stringify_attributes(array('width' => '800', 'height' => '600'), TRUE));

		$atts = new stdClass;
		$atts->width = 800;
		$atts->height = 600;
		$this->assertEquals('width=800,height=600', stringify_attributes($atts, TRUE));
	}

	// ------------------------------------------------------------------------


}
