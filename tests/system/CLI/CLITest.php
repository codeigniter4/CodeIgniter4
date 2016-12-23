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
