<?php namespace CodeIgniter\CLI;

use CodeIgniter\Test\CIUnitTestCase;

class CLITest extends CIUnitTestCase
{

	public function testBeep()
	{
		$this->expectOutputString("\x07");
		CLI::beep();
	}

	public function testBeep4()
	{
		$this->expectOutputString("\x07\x07\x07\x07");
		CLI::beep(4);
	}

	public function testIsWindows()
	{
		$this->assertEquals(('\\' === DIRECTORY_SEPARATOR), CLI::isWindows());
		$this->assertEquals(defined('PHP_WINDOWS_VERSION_MAJOR'), CLI::isWindows());
	}

	public function testWrap()
	{
		$this->assertEquals('', CLI::wrap(''));
		$this->assertEquals("1234\n 5678\n 90\n abc\n de\n fghij\n 0987654321", CLI::wrap("1234 5678 90\nabc de fghij\n0987654321", 5, 1));
		$this->assertEquals("1234 5678 90\n  abc de fghij\n  0987654321", CLI::wrap("1234 5678 90\nabc de fghij\n0987654321", 999, 2));
		$this->assertEquals("1234 5678 90\nabc de fghij\n0987654321", CLI::wrap("1234 5678 90\nabc de fghij\n0987654321"));
	}

	public function testParseCommand()
	{
		$_SERVER['argv'] = [
			'ignored',
			'b',
			'c',
		];
		$_SERVER['argc'] = 3;
		CLI::init();
		$this->assertEquals(null, CLI::getSegment(3));
		$this->assertEquals('b', CLI::getSegment(1));
		$this->assertEquals('c', CLI::getSegment(2));
		$this->assertEquals('b/c', CLI::getURI());
		$this->assertEquals([], CLI::getOptions());
		$this->assertEmpty(CLI::getOptionString());
		$this->assertEquals(['b', 'c'], CLI::getSegments());
	}

	public function testParseCommandMixed()
	{
		$_SERVER['argv'] = [
			'ignored',
			'b',
			'c',
			'd',
			'-parm',
			'pvalue',
			'd2',
		];
		$_SERVER['argc'] = 7;
		CLI::init();
		$this->assertEquals(null, CLI::getSegment(7));
		$this->assertEquals('b', CLI::getSegment(1));
		$this->assertEquals('c', CLI::getSegment(2));
		$this->assertEquals('d', CLI::getSegment(3));
		$this->assertEquals(['b', 'c', 'd', 'd2'], CLI::getSegments());
	}

	public function testParseCommandOption()
	{
		$_SERVER['argv'] = [
			'ignored',
			'b',
			'c',
			'-parm',
			'pvalue',
			'd',
		];
		$_SERVER['argc'] = 6;
		CLI::init();
		$this->assertEquals(['parm' => 'pvalue'], CLI::getOptions());
		$this->assertEquals('pvalue', CLI::getOption('parm'));
		$this->assertEquals('-parm pvalue ', CLI::getOptionString());
		$this->assertNull(CLI::getOption('bogus'));
		$this->assertEquals(['b', 'c', 'd'], CLI::getSegments());
	}

	public function testParseCommandMultipleOptions()
	{
		$_SERVER['argv'] = [
			'ignored',
			'b',
			'c',
			'-parm',
			'pvalue',
			'd',
			'-p2',
			'-p3',
			'value 3',
		];
		$_SERVER['argc'] = 9;
		CLI::init();
		$this->assertEquals(['parm' => 'pvalue', 'p2' => null, 'p3' => 'value 3'], CLI::getOptions());
		$this->assertEquals('pvalue', CLI::getOption('parm'));
		$this->assertEquals('-parm pvalue -p2  -p3 "value 3" ', CLI::getOptionString());
		$this->assertEquals(['b', 'c', 'd'], CLI::getSegments());
	}

	public function testWindow()
	{
		$this->assertTrue(is_int(CLI::getHeight()));
		$this->assertTrue(is_int(CLI::getWidth()));
	}
}
