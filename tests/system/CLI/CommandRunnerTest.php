<?php
namespace CodeIgniter\CLI;

use CodeIgniter\HTTP\UserAgent;
use Config\Services;
use Tests\Support\Config\MockCLIConfig;
use CodeIgniter\Test\Filters\CITestStreamFilter;

class CommandRunnerTest extends \CIUnitTestCase
{

	private $stream_filter;
	protected $env;
	protected $config;
	protected $request;
	protected $response;
	protected $logger;
	protected $runner;

	protected function setUp()
	{
		parent::setUp();

		CITestStreamFilter::$buffer = '';
		$this->stream_filter        = stream_filter_append(STDOUT, 'CITestStreamFilter');

		$this->env = new \CodeIgniter\Config\DotEnv(ROOTPATH);
		$this->env->load();

		// Set environment values that would otherwise stop the framework from functioning during tests.
		if (! isset($_SERVER['app.baseURL']))
		{
			$_SERVER['app.baseURL'] = 'http://example.com';
		}

		$_SERVER['argv'] = [
			'spark',
			'list',
		];
		$_SERVER['argc'] = 2;
		CLI::init();

		$this->config   = new MockCLIConfig();
		$this->request  = new \CodeIgniter\HTTP\IncomingRequest($this->config, new \CodeIgniter\HTTP\URI('https://somwhere.com'), null, new UserAgent());
		$this->response = new \CodeIgniter\HTTP\Response($this->config);
		$this->logger   = Services::logger();
		$this->runner   = new CommandRunner();
		$this->runner->initController($this->request, $this->response, $this->logger);
	}

	public function tearDown()
	{
		stream_filter_remove($this->stream_filter);
	}

	public function testGoodCommand()
	{
		$this->runner->index(['list']);
		$result = CITestStreamFilter::$buffer;

		// make sure the result looks like a command list
		$this->assertContains('Lists the available commands.', $result);
		$this->assertContains('Displays basic usage information.', $result);
	}

	public function testDefaultCommand()
	{
		$this->runner->index([]);
		$result = CITestStreamFilter::$buffer;

		// make sure the result looks like basic help
		$this->assertContains('Displays basic usage information.', $result);
		$this->assertContains('help command_name', $result);
	}

	public function testHelpCommand()
	{
		$this->runner->index(['help']);
		$result = CITestStreamFilter::$buffer;

		// make sure the result looks like basic help
		$this->assertContains('Displays basic usage information.', $result);
		$this->assertContains('help command_name', $result);
	}

	public function testHelpCommandDetails()
	{
		$this->runner->index(['help', 'session:migration']);
		$result = CITestStreamFilter::$buffer;

		// make sure the result looks like more detailed help
		$this->assertContains('Description:', $result);
		$this->assertContains('Usage:', $result);
		$this->assertContains('Options:', $result);
	}

	public function testCommandProperties()
	{
		$this->runner->index(['help']);
		$result   = CITestStreamFilter::$buffer;
		$commands = $this->runner->getCommands();
		$command  = new $commands['help']['class']($this->logger, $this->runner);

		$this->assertEquals('Displays basic usage information.', $command->description);
		$this->assertNull($command->notdescription);
	}

	public function testEmptyCommand()
	{
		$this->runner->index([null, 'list']);
		$result = CITestStreamFilter::$buffer;

		// make sure the result looks like a command list
		$this->assertContains('Lists the available commands.', $result);
	}

	public function testBadCommand()
	{
		$this->error_filter = stream_filter_append(STDERR, 'CITestStreamFilter');
		$this->runner->index(['bogus']);
		$result = CITestStreamFilter::$buffer;
		stream_filter_remove($this->error_filter);

		// make sure the result looks like a command list
		$this->assertContains('Command "bogus" not found', $result);
	}

}
