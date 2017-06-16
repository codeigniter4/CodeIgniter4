<?php namespace CodeIgniter\CLI;

class CLITest extends \CIUnitTestCase
{
	private $stream_filter;

	public function setUp()
	{
		CLITestStreamFilter::$buffer = '';
		$this->stream_filter = stream_filter_append(STDOUT, 'CLITestStreamFilter');
	}

	public function tearDown()
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
		$this->assertEquals(1, time() - $time);

		$time = time();
		CLI::wait(1);
		$this->assertEquals(1, time() - $time);
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

	/**
	 * @expectedException        RuntimeException
	 * @expectedExceptionMessage Invalid CLI foreground color: Foreground
	 */
	public function testColorExceptionForeground()
	{
		CLI::color('test', 'Foreground');
	}

	/**
	 * @expectedException        RuntimeException
	 * @expectedExceptionMessage Invalid CLI background color: Background
	 */
	public function testColorExceptionBackground()
	{
		CLI::color('test', 'white', 'Background');
	}

	public function testColor()
	{
		$this->assertEquals("\033[1;37m\033[42m\033[4mtest\033[0m", CLI::color('test', 'white', 'green', 'underline'));
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

		$expected = <<<EOT
first.
[\033[32m#.........\033[0m]   5% Complete
\033[1A[\033[32m#####.....\033[0m]  50% Complete
\033[1A[\033[32m##########\033[0m] 100% Complete
second.
[\033[32m#.........\033[0m]   5% Complete
\033[1A[\033[32m#####.....\033[0m]  50% Complete
\033[1A[\033[32m##########\033[0m] 100% Complete
third.
[\033[32m#.........\033[0m]   5% Complete

EOT;
		$this->assertEquals($expected, CLITestStreamFilter::$buffer);
	}

	public function testShowProgressWithoutBar()
	{
		CLI::write('first.');
		CLI::showProgress(false, 20);
		CLI::showProgress(false, 20);
		CLI::showProgress(false, 20);

		$expected = <<<EOT
first.
\007\007\007
EOT;
		$this->assertEquals($expected, CLITestStreamFilter::$buffer);
	}

	public function testWrap()
	{
		$this->assertEquals('', CLI::wrap(''));
		$this->assertEquals('1234'. PHP_EOL .' 5678'. PHP_EOL .' 90'. PHP_EOL .' abc'. PHP_EOL .' de'. PHP_EOL .' fghij'. PHP_EOL .' 0987654321', CLI::wrap('1234 5678 90'. PHP_EOL .'abc de fghij'. PHP_EOL .'0987654321', 5, 1));
		$this->assertEquals('1234 5678 90'. PHP_EOL .'  abc de fghij'. PHP_EOL .'  0987654321', CLI::wrap('1234 5678 90'. PHP_EOL .'abc de fghij'. PHP_EOL .'0987654321', 999, 2));
		$this->assertEquals('1234 5678 90'. PHP_EOL .'abc de fghij'. PHP_EOL .'0987654321', CLI::wrap('1234 5678 90'. PHP_EOL .'abc de fghij'. PHP_EOL .'0987654321'));
	}
}


class CLITestStreamFilter extends \php_user_filter
{
	public static $buffer = '';

	public function filter($in, $out, &$consumed, $closing)
	{
		while ($bucket = stream_bucket_make_writeable($in)) {
			self::$buffer .= $bucket->data;
			$consumed += $bucket->datalen;
		}
		return PSFS_PASS_ON;
	}
}

stream_filter_register('CLITestStreamFilter', 'CodeIgniter\CLI\CLITestStreamFilter');
