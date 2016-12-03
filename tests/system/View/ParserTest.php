<?php

use CodeIgniter\View\Parser;

class ParserTest extends \CIUnitTestCase
{

	public function setUp()
	{
		$this->loader = new \CodeIgniter\Autoloader\FileLocator(new \Config\Autoload());
		$this->viewsDir = __DIR__.'/Views';
        $this->config   = new Config\View();
	}

	// --------------------------------------------------------------------

	public function testSetDelimiters()
	{
		$parser = new Parser($this->config, $this->viewsDir, $this->loader);

		// Make sure default delimiters are there
		$this->assertEquals('{', $parser->leftDelimiter);
		$this->assertEquals('}', $parser->rightDelimiter);

		// Change them to square brackets
		$parser->setDelimiters('[', ']');

		// Make sure they changed
		$this->assertEquals('[', $parser->leftDelimiter);
		$this->assertEquals(']', $parser->rightDelimiter);

		// Reset them
		$parser->setDelimiters();

		// Make sure default delimiters are there
		$this->assertEquals('{', $parser->leftDelimiter);
		$this->assertEquals('}', $parser->rightDelimiter);
	}

	// --------------------------------------------------------------------

	public function testParseSimple()
	{
		$parser = new Parser($this->config, $this->viewsDir, $this->loader);
		$parser->setVar('teststring', 'Hello World');

		$expected = '<h1>Hello World</h1>';
		$this->assertEquals($expected, $parser->render('template1'));
	}

	// --------------------------------------------------------------------

	public function testParseString()
	{
		$parser = new Parser($this->config, $this->viewsDir, $this->loader);
		$data = array (
			'title'	 => 'Page Title',
			'body'	 => 'Lorem ipsum dolor sit amet.'
		);

		$template = "{title}\n{body}";

		$result = implode("\n", $data);

		$parser->setData($data);
		$this->assertEquals($result, $parser->renderString($template));
	}

	// --------------------------------------------------------------------

	public function testParseStringMissingData()
	{
		$parser = new Parser($this->config, $this->viewsDir, $this->loader);
		$data = array (
			'title'	 => 'Page Title',
			'body'	 => 'Lorem ipsum dolor sit amet.'
		);

		$template = "{title}\n{body}\n{name}";

		$result = implode("\n", $data)."\n{name}";

		$parser->setData($data);
		$this->assertEquals($result, $parser->renderString($template));
	}

	// --------------------------------------------------------------------

	public function testParseStringUnusedData()
	{
		$parser = new Parser($this->config, $this->viewsDir, $this->loader);
		$data = array (
			'title'	 => 'Page Title',
			'body'	 => 'Lorem ipsum dolor sit amet.',
			'name'	 => 'Someone'
		);

		$template = "{title}\n{body}";

		$result = "Page Title\nLorem ipsum dolor sit amet.";

		$parser->setData($data);
		$this->assertEquals($result, $parser->renderString($template));
	}

	// --------------------------------------------------------------------

	public function testParseNoTemplate()
	{
		$parser = new Parser($this->config, $this->viewsDir, $this->loader);
		$this->assertEquals('', $parser->renderString(''));
	}

	// --------------------------------------------------------------------

	public function testParseNested()
	{
		$parser = new Parser($this->config, $this->viewsDir, $this->loader);
		$data = array (
			'title'	 => 'Super Heroes',
			'powers' => array (
				array ('invisibility' => 'yes', 'flying' => 'no')
			)
		);

		$template = "{title}\n{powers}{invisibility}\n{flying}{/powers}\nsecond:{powers} {invisibility} {flying}{/powers}";

		$parser->setData($data);
		$this->assertEquals("Super Heroes\nyes\nno\nsecond: yes no", $parser->renderString($template));
	}

	// --------------------------------------------------------------------

	public function testParseLoop()
	{
		$parser = new Parser($this->config, $this->viewsDir, $this->loader);
		$data = array (
			'title'	 => 'Super Heroes',
			'powers' => array (
				array ('name' => 'Tom'),
				array ('name' => 'Dick'),
				array ('name' => 'Henry')
			)
		);

		$template = "{title}\n{powers}{name} {/powers}";

		$parser->setData($data);
		$this->assertEquals("Super Heroes\nTom Dick Henry ", $parser->renderString($template));
	}

	// --------------------------------------------------------------------

	public function testMismatchedVarPair()
	{
		$parser = new Parser($this->config, $this->viewsDir, $this->loader);
		$data = array (
			'title'	 => 'Super Heroes',
			'powers' => array (
				array ('invisibility' => 'yes', 'flying' => 'no')
			)
		);

		$template = "{title}\n{powers}{invisibility}\n{flying}";
		$result = "Super Heroes\n{powers}{invisibility}\n{flying}";

		$parser->setData($data);
		$this->assertEquals($result, $parser->renderString($template));
	}

		// Test anchor

	public function escValueTypes()
	{
		return [
			'scalar'		 => [42],
			'string'		 => ['George'],
			'scalarlist'	 => [ [1, 2, 17, -4]],
			'stringlist'	 => [ ['George', 'Paul', 'John', 'Ringo']],
			'associative'	 => [ ['name' => 'George', 'role' => 'guitar']],
			'compound'		 => [ ['name' => 'George', 'address' => ['line1' => '123 Some St', 'planet' => 'Naboo']]],
			'pseudo'		 => [ ['name'	 => 'George', 'emails' => [
						['email' => 'me@here.com', 'type' => 'home'],
						['email' => 'me@there.com', 'type' => 'work']
					]]],
		];
	}

	/**
	 * @dataProvider escValueTypes
	 */
	public function testEscHandling($value, $expected = null)
	{
		if ($expected == null) $expected = $value;
		$this->assertEquals($expected, \esc($value, 'html'));
	}

	// ------------------------------------------------------------------------

}
