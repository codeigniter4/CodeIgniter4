<?php namespace CodeIgniter\Commands;

use Config\MockAppConfig;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\CommandRunner;

class CommandsTest extends \CIUnitTestCase
{

	private $stream_filter;

	public function setUp()
	{
		CommandsTestStreamFilter::$buffer = '';
		$this->stream_filter = stream_filter_append(STDOUT, 'CommandsTestStreamFilter');

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

		$this->config = new MockAppConfig();
		$this->request = new \CodeIgniter\HTTP\IncomingRequest($this->config, new \CodeIgniter\HTTP\URI('https://somwhere.com'), null, new UserAgent());
		$this->response = new \CodeIgniter\HTTP\Response($this->config);
		$this->runner = new CommandRunner($this->request, $this->response);
	}

	public function tearDown()
	{
		stream_filter_remove($this->stream_filter);
	}

	public function testHelpCommand()
	{
		$this->runner->index(['help']);
		$result = CommandsTestStreamFilter::$buffer;

		// make sure the result looks like a command list
		$this->assertContains('Displays basic usage information.', $result);
		$this->assertContains('command_name', $result);
	}

	public function testListCommands()
	{
		$this->runner->index(['list']);
		$result = CommandsTestStreamFilter::$buffer;

		// make sure the result looks like a command list
		$this->assertContains('Lists the available commands.', $result);
		$this->assertContains('Displays basic usage information.', $result);
	}

}
