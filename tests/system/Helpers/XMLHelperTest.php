<?php

namespace CodeIgniter\Helpers;

class XMLHelperTest extends \CIUnitTestCase
{

	protected function setUp()
	{
		parent::setUp();

		helper('xml');
	}

	// --------------------------------------------------------------------

	public function testConvert()
	{
		$original = '<p>Here is a so-so paragraph & an entity (&#123;).</p>';
		$expected = '&lt;p&gt;Here is a so&#45;so paragraph &amp; an entity (&#123;).&lt;/p&gt;';
		$this->assertEquals($expected, xml_convert($original));
	}

	// --------------------------------------------------------------------
	public function testConvertProtected()
	{
		$original = '<p>Here is a so&so; paragraph & an entity (&#123;).</p>';
		$expected = '&lt;p&gt;Here is a so&so; paragraph &amp; an entity (&#123;).&lt;/p&gt;';
		$this->assertEquals($expected, xml_convert($original, true));
	}

}
