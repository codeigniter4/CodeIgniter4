<?php namespace CodeIgniter\CLI;

use CodeIgniter\Test\Filters\CITestStreamFilter;

class CLITest extends \CodeIgniter\Test\CIUnitTestCase
{

	private $stream_filter;

	protected function setUp(): void
	{
		parent::setUp();

		CITestStreamFilter::$buffer = '';
		$this->stream_filter        = stream_filter_append(STDOUT, 'CITestStreamFilter');
	}

	public function tearDown(): void
	{
		stream_filter_remove($this->stream_filter);
	}

	public function testNew()
	{
		$actual = new CLI();
		$this->assertInstanceOf(CLI::class, $actual);
	}

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

	public function testWait()
	{
		$time = time();
		CLI::wait(1, true);
		$this->assertCloseEnough(1, time() - $time);

		$time = time();
		CLI::wait(1);
		$this->assertCloseEnough(1, time() - $time);

		// Leaving the code fragment below in, to remind myself (or others)
		// of what appears to be the most likely path to test this last
		// bit of wait() functionality.
		// The problem: if the block below is enabled, the phpunit tests
		// go catatonic when it is executed, presumably because of
		// the CLI::input() waiting for a key press
		//      // test the press any key to continue...
		//      stream_filter_register('CLITestKeyboardFilter', 'CodeIgniter\CLI\CLITestKeyboardFilter');
		//      $spoofer = stream_filter_append(STDIN, 'CLITestKeyboardFilter');
		//      $time = time();
		//      CLITestKeyboardFilter::$spoofed = ' ';
		//      CLI::wait(0);
		//      stream_filter_remove($spoofer);
		//      $this->assertEquals(0, time() - $time);
	}

	public function testIsWindows()
	{
		$this->assertEquals(('\\' === DIRECTORY_SEPARATOR), CLI::isWindows());
		$this->assertEquals(defined('PHP_WINDOWS_VERSION_MAJOR'), CLI::isWindows());
	}

	public function testNewLine()
	{
		$this->expectOutputString('');
		CLI::newLine();
	}

	public function testColorExceptionForeground()
	{
		$this->expectException('RuntimeException');
		$this->expectExceptionMessage('Invalid foreground color: Foreground');

		CLI::color('test', 'Foreground');
	}

	public function testColorExceptionBackground()
	{
		$this->expectException('RuntimeException');
		$this->expectExceptionMessage('Invalid background color: Background');

		CLI::color('test', 'white', 'Background');
	}

	public function testColorSupportOnNoColor()
	{
		$nocolor = getenv('NO_COLOR');
		putenv('NO_COLOR=1');

		CLI::init(); // force re-check on env
		$this->assertEquals('test', CLI::color('test', 'white', 'green'));
		putenv($nocolor ? "NO_COLOR=$nocolor" : 'NO_COLOR');
	}

	public function testColorSupportOnHyperTerminals()
	{
		$termProgram = getenv('TERM_PROGRAM');
		putenv('TERM_PROGRAM=Hyper');

		CLI::init(); // force re-check on env
		$this->assertEquals("\033[1;37m\033[42m\033[4mtest\033[0m", CLI::color('test', 'white', 'green', 'underline'));
		putenv($termProgram ? "TERM_PROGRAM=$termProgram" : 'TERM_PROGRAM');
	}

	public function testStreamSupports()
	{
		$this->assertTrue(CLI::streamSupports('stream_isatty', STDOUT));
		$this->assertIsBool(CLI::streamSupports('sapi_windows_vt100_support', STDOUT));
	}

	public function testColor()
	{
		// After the tests on NO_COLOR and TERM_PROGRAM above,
		// the $isColored variable is rigged. So we reset this.
		CLI::init();
		$this->assertEquals("\033[1;37m\033[42m\033[4mtest\033[0m", CLI::color('test', 'white', 'green', 'underline'));
	}

	public function testPrint()
	{
		CLI::print('test');
		$expected = 'test';

		$this->assertEquals($expected, CITestStreamFilter::$buffer);
	}

	public function testPrintForeground()
	{
		CLI::print('test', 'red');
		$expected = "\033[0;31mtest\033[0m";

		$this->assertEquals($expected, CITestStreamFilter::$buffer);
	}

	public function testPrintBackground()
	{
		CLI::print('test', 'red', 'green');
		$expected = "\033[0;31m\033[42mtest\033[0m";

		$this->assertEquals($expected, CITestStreamFilter::$buffer);
	}

	public function testWrite()
	{
		CLI::write('test');
		$expected = PHP_EOL . 'test' . PHP_EOL;
		$this->assertEquals($expected, CITestStreamFilter::$buffer);
	}

	public function testWriteForeground()
	{
		CLI::write('test', 'red');
		$expected = "\033[0;31mtest\033[0m" . PHP_EOL;
		$this->assertEquals($expected, CITestStreamFilter::$buffer);
	}

	public function testWriteForegroundWithColorBefore()
	{
		CLI::write(CLI::color('green', 'green') . ' red', 'red');
		$expected = "\033[0;31m\033[0;32mgreen\033[0m\033[0;31m red\033[0m" . PHP_EOL;
		$this->assertEquals($expected, CITestStreamFilter::$buffer);
	}

	public function testWriteForegroundWithColorAfter()
	{
		CLI::write('red ' . CLI::color('green', 'green'), 'red');
		$expected = "\033[0;31mred \033[0;32mgreen\033[0m" . PHP_EOL;
		$this->assertEquals($expected, CITestStreamFilter::$buffer);
	}

	public function testWriteBackground()
	{
		CLI::write('test', 'red', 'green');
		$expected = "\033[0;31m\033[42mtest\033[0m" . PHP_EOL;
		$this->assertEquals($expected, CITestStreamFilter::$buffer);
	}

	public function testError()
	{
		$this->stream_filter = stream_filter_append(STDERR, 'CITestStreamFilter');
		CLI::error('test');
		// red expected cuz stderr
		$expected = "\033[1;31mtest\033[0m" . PHP_EOL;
		$this->assertEquals($expected, CITestStreamFilter::$buffer);
	}

	public function testErrorForeground()
	{
		$this->stream_filter = stream_filter_append(STDERR, 'CITestStreamFilter');
		CLI::error('test', 'purple');
		$expected = "\033[0;35mtest\033[0m" . PHP_EOL;
		$this->assertEquals($expected, CITestStreamFilter::$buffer);
	}

	public function testErrorBackground()
	{
		$this->stream_filter = stream_filter_append(STDERR, 'CITestStreamFilter');
		CLI::error('test', 'purple', 'green');
		$expected = "\033[0;35m\033[42mtest\033[0m" . PHP_EOL;
		$this->assertEquals($expected, CITestStreamFilter::$buffer);
	}

	public function testShowProgress()
	{
		CLI::write('first.');
		CLI::showProgress(1, 20);
		CLI::showProgress(10, 20);
		CLI::showProgress(20, 20);
		CLI::write('second.');
		CLI::showProgress(1, 20);
		CLI::showProgress(10, 20);
		CLI::showProgress(20, 20);
		CLI::write('third.');
		CLI::showProgress(1, 20);

		$expected = 'first.' . PHP_EOL .
					"[\033[32m#.........\033[0m]   5% Complete" . PHP_EOL .
					"\033[1A[\033[32m#####.....\033[0m]  50% Complete" . PHP_EOL .
					"\033[1A[\033[32m##########\033[0m] 100% Complete" . PHP_EOL .
					'second.' . PHP_EOL .
					"[\033[32m#.........\033[0m]   5% Complete" . PHP_EOL .
					"\033[1A[\033[32m#####.....\033[0m]  50% Complete" . PHP_EOL .
					"\033[1A[\033[32m##########\033[0m] 100% Complete" . PHP_EOL .
					'third.' . PHP_EOL .
					"[\033[32m#.........\033[0m]   5% Complete" . PHP_EOL;
		$this->assertEquals($expected, CITestStreamFilter::$buffer);
	}

	public function testShowProgressWithoutBar()
	{
		CLI::write('first.');
		CLI::showProgress(false, 20);
		CLI::showProgress(false, 20);
		CLI::showProgress(false, 20);

		$expected = 'first.' . PHP_EOL . "\007\007\007";
		$this->assertEquals($expected, CITestStreamFilter::$buffer);
	}

	public function testWrap()
	{
		$this->assertEquals('', CLI::wrap(''));
		$this->assertEquals('1234' . PHP_EOL . ' 5678' . PHP_EOL . ' 90' . PHP_EOL . ' abc' . PHP_EOL . ' de' . PHP_EOL . ' fghij' . PHP_EOL . ' 0987654321', CLI::wrap('1234 5678 90' . PHP_EOL . 'abc de fghij' . PHP_EOL . '0987654321', 5, 1));
		$this->assertEquals('1234 5678 90' . PHP_EOL . '  abc de fghij' . PHP_EOL . '  0987654321', CLI::wrap('1234 5678 90' . PHP_EOL . 'abc de fghij' . PHP_EOL . '0987654321', 999, 2));
		$this->assertEquals('1234 5678 90' . PHP_EOL . 'abc de fghij' . PHP_EOL . '0987654321', CLI::wrap('1234 5678 90' . PHP_EOL . 'abc de fghij' . PHP_EOL . '0987654321'));
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
			'da-sh',
			'-fix',
			'-opt-in',
			'sure',
		];
		CLI::init();
		$this->assertEquals(null, CLI::getSegment(7));
		$this->assertEquals('b', CLI::getSegment(1));
		$this->assertEquals('c', CLI::getSegment(2));
		$this->assertEquals('d', CLI::getSegment(3));
		$this->assertEquals(['b', 'c', 'd', 'd2', 'da-sh'], CLI::getSegments());
		$this->assertEquals(['parm' => 'pvalue', 'fix' => null, 'opt-in' => 'sure'], CLI::getOptions());
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
		CLI::init();
		$this->assertEquals(['parm' => 'pvalue', 'p2' => null, 'p3' => 'value 3'], CLI::getOptions());
		$this->assertEquals('pvalue', CLI::getOption('parm'));
		$this->assertEquals('-parm pvalue -p2  -p3 "value 3" ', CLI::getOptionString());
		$this->assertEquals(['b', 'c', 'd'], CLI::getSegments());
	}

	public function testWindow()
	{
		$height = new \ReflectionProperty(CLI::class, 'height');
		$height->setAccessible(true);
		$height->setValue(null);
		$this->assertTrue(is_int(CLI::getHeight()));

		$width = new \ReflectionProperty(CLI::class, 'width');
		$width->setAccessible(true);
		$width->setValue(null);
		$this->assertTrue(is_int(CLI::getWidth()));
	}

	/**
	 * @dataProvider tableProvider
	 *
	 * @param array $tbody
	 * @param array $thead
	 * @param array $expected
	 */
	public function testTable($tbody, $thead, $expected)
	{
		CLI::table($tbody, $thead);
		$this->assertEquals(CITestStreamFilter::$buffer, $expected);
	}

	public function tableProvider()
	{
		$head      = [
			'ID',
			'Title',
		];
		$one_row   = [
			[
				'id'  => 1,
				'foo' => 'bar',
			],
		];
		$many_rows = [
			[
				'id'  => 1,
				'foo' => 'bar',
			],
			[
				'id'  => 2,
				'foo' => 'bar * 2',
			],
			[
				'id'  => 3,
				'foo' => 'bar + bar + bar',
			],
		];

		return [
			[
				$one_row,
				[],
				'+---+-----+' . PHP_EOL .
				'| 1 | bar |' . PHP_EOL .
				'+---+-----+' . PHP_EOL . PHP_EOL,
			],
			[
				$one_row,
				$head,
				'+----+-------+' . PHP_EOL .
				'| ID | Title |' . PHP_EOL .
				'+----+-------+' . PHP_EOL .
				'| 1  | bar   |' . PHP_EOL .
				'+----+-------+' . PHP_EOL . PHP_EOL,
			],
			[
				$many_rows,
				[],
				'+---+-----------------+' . PHP_EOL .
				'| 1 | bar             |' . PHP_EOL .
				'| 2 | bar * 2         |' . PHP_EOL .
				'| 3 | bar + bar + bar |' . PHP_EOL .
				'+---+-----------------+' . PHP_EOL . PHP_EOL,
			],
			[
				$many_rows,
				$head,
				'+----+-----------------+' . PHP_EOL .
				'| ID | Title           |' . PHP_EOL .
				'+----+-----------------+' . PHP_EOL .
				'| 1  | bar             |' . PHP_EOL .
				'| 2  | bar * 2         |' . PHP_EOL .
				'| 3  | bar + bar + bar |' . PHP_EOL .
				'+----+-----------------+' . PHP_EOL . PHP_EOL,
			],
		];
	}

	public function testStrlen()
	{
		$this->assertEquals(18, mb_strlen(CLI::color('success', 'green')));
		$this->assertEquals(7, CLI::strlen(CLI::color('success', 'green')));
		$this->assertEquals(0, CLI::strlen(null));
	}
}
