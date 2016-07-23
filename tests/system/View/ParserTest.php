<?php

use CodeIgniter\View\Parser;

class ParserTest extends \CIUnitTestCase {

	public function setUp()
	{
		$this->loader = new \CodeIgniter\Autoloader\FileLocator(new \Config\Autoload());
		$this->viewsDir = __DIR__.'/Views';
	    $this->parser = new Parser($this->viewsDir, $this->loader);
	}

	// --------------------------------------------------------------------

	public function testSetDelimiters()
	{
		
		// Make sure default delimiters are there
		$this->assertEquals('{', $this->parser->leftDelimiter);
		$this->assertEquals('}', $this->parser->rightDelimiter);

		// Change them to square brackets
		$this->parser->setDelimiters('[', ']');

		// Make sure they changed
		$this->assertEquals('[', $this->parser->leftDelimiter);
		$this->assertEquals(']', $this->parser->rightDelimiter);

		// Reset them
		$this->parser->setDelimiters();

		// Make sure default delimiters are there
		$this->assertEquals('{', $this->parser->leftDelimiter);
		$this->assertEquals('}', $this->parser->rightDelimiter);
	}

	// --------------------------------------------------------------------

	public function testParseString()
	{
		$data = array(
			'title' => 'Page Title',
			'body' => 'Lorem ipsum dolor sit amet.'
		);

		$template = "{title}\n{body}";

		$result = implode("\n", $data);

		$this->assertEquals($result, $this->parser->renderString($template, $data, TRUE));
	}

	// --------------------------------------------------------------------

	public function testParse()
	{
		$this->_parseNoTemplate();
		$this->_parseVarPair();
		$this->_mismatchedVarPair();
	}

	// --------------------------------------------------------------------

	private function _parseNoTemplate()
	{
		$this->assertFalse($this->parser->renderString('', '', TRUE));
	}

	// --------------------------------------------------------------------

	private function _parseVarPair()
	{
		$data = array(
			'title'		=> 'Super Heroes',
			'powers'	=> array(array('invisibility' => 'yes', 'flying' => 'no'))
		);

		$template = "{title}\n{powers}{invisibility}\n{flying}{/powers}\nsecond:{powers} {invisibility} {flying}{/powers}";

		$this->assertEquals("Super Heroes\nyes\nno\nsecond: yes no", $this->parser->renderString($template, $data, TRUE));
	}

	// --------------------------------------------------------------------

	private function _mismatchedVarPair()
	{
		$data = array(
			'title'		=> 'Super Heroes',
			'powers'	=> array(array('invisibility' => 'yes', 'flying' => 'no'))
		);

		$template = "{title}\n{powers}{invisibility}\n{flying}";
		$result = "Super Heroes\n{powers}{invisibility}\n{flying}";

		$this->assertEquals($result, $this->parser->renderString($template, $data, TRUE));
	}

}