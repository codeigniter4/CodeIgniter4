<?php namespace CodeIgniter\CLI;

use Config\MockCLIConfig;
use CodeIgniter\HTTP\UserAgent;

class CommandRunnerTest extends \CIUnitTestCase
{

	private $stream_filter;

	public function setUp()
	{
		CLICommandStreamFilter::$buffer = '';
		$this->stream_filter = stream_filter_append(STDOUT, 'CLICommandStreamFilter');

		$this->env = new \CodeIgniter\Config\DotEnv(ROOTPATH);
		$this->env->load();

		// Set environment values that would otherwise stop the framework from functioning during tests.
		if ( ! isset($_SERVER['app.baseURL']))
		{
			$_SERVER['app.baseURL'] = 'http://example.com';
		}

		$_SERVER['argv'] = ['spark', 'list'];
		$_SERVER['argc'] = 2;
		CLI::init();

		$this->config = new MockCLIConfig();
		$this->request = new \CodeIgniter\HTTP\IncomingRequest($this->config, new \CodeIgniter\HTTP\URI('https://somwhere.com'), null, new UserAgent());
		$this->response = new \CodeIgniter\HTTP\Response($this->config);
		$this->runner = new CommandRunner($this->request, $this->response);
	}

	public function tearDown()
	{
		stream_filter_remove($this->stream_filter);
	}

	public function testGoodCommand()
	{
		$this->runner->index(['list']);
		$result = CLICommandStreamFilter::$buffer;

		// make sure the result looks like a command list
		$this->assertContains('Lists the available commands.', $result);
		$this->assertContains('Displays basic usage information.', $result);
	}

	public function testDefaultCommand()
	{
		$this->runner->index([]);
		$result = CLICommandStreamFilter::$buffer;

		// make sure the result looks like basic help
		$this->assertContains('Displays basic usage information.', $result);
		$this->assertContains('help command_name', $result);
	}

	public function testEmptyCommand()
	{
		$this->runner->index([null,'list']);
		$result = CLICommandStreamFilter::$buffer;

		// make sure the result looks like a command list
		$this->assertContains('Lists the available commands.', $result);
	}

	public function testBadCommand()
	{
		$this->error_filter = stream_filter_append(STDERR, 'CLICommandStreamFilter');
		$this->runner->index(['bogus']);
		$result = CLICommandStreamFilter::$buffer;
		stream_filter_remove($this->error_filter);

		// make sure the result looks like a command list
		$this->assertContains("Command 'bogus' not found", $result);
	}

}

class CLICommandStreamFilter extends \php_user_filter
{

	public static $buffer = '';

	public function filter($in, $out, &$consumed, $closing)
	{
		while ($bucket = stream_bucket_make_writeable($in))
		{
			self::$buffer .= $bucket->data;
			$consumed += $bucket->datalen;
		}
		return PSFS_PASS_ON;
	}

}

stream_filter_register('CLICommandStreamFilter', 'CodeIgniter\CLI\CLICommandStreamFilter');
