<?php
namespace CodeIgniter\Commands;

use Config\Services;
use CodeIgniter\Test\Mock\MockAppConfig;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\CommandRunner;
use CodeIgniter\Test\Filters\CITestStreamFilter;

class CommandsTest extends \CodeIgniter\Test\CIUnitTestCase
{

	private $stream_filter;
	protected $env;
	protected $config;
	protected $request;
	protected $response;
	protected $logger;
	protected $runner;

	protected function setUp(): void
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

		$this->config   = new MockAppConfig();
		$this->request  = new \CodeIgniter\HTTP\IncomingRequest($this->config, new \CodeIgniter\HTTP\URI('https://somwhere.com'), null, new UserAgent());
		$this->response = new \CodeIgniter\HTTP\Response($this->config);
		$this->logger   = Services::logger();
		$this->runner   = new CommandRunner();
		$this->runner->initController($this->request, $this->response, $this->logger);
	}

	public function tearDown(): void
	{
		stream_filter_remove($this->stream_filter);
	}

	public function testHelpCommand()
	{
		$this->runner->index(['help']);
		$result = CITestStreamFilter::$buffer;

		// make sure the result looks like a command list
		$this->assertStringContainsString('Displays basic usage information.', $result);
		$this->assertStringContainsString('command_name', $result);
	}

	public function testListCommands()
	{
		$this->runner->index(['list']);
		$result = CITestStreamFilter::$buffer;

		// make sure the result looks like a command list
		$this->assertStringContainsString('Lists the available commands.', $result);
		$this->assertStringContainsString('Displays basic usage information.', $result);
	}

	public function testCustomCommand()
	{
		$this->runner->index(['app:info']);
		$result = CITestStreamFilter::$buffer;

		$this->assertStringContainsString('CI Version:', $result);
	}

	public function testShowError()
	{
		$this->runner->index(['app:info']);
		$commands = $this->runner->getCommands();
		$command  = new $commands['app:info']['class']($this->logger, $this->runner);

		$command->helpme();
		$result = CITestStreamFilter::$buffer;
		$this->assertStringContainsString('Displays basic usage information.', $result);
	}

	public function testCommandCall()
	{
		$this->error_filter = stream_filter_append(STDERR, 'CITestStreamFilter');
		$this->runner->index(['app:info']);
		$commands = $this->runner->getCommands();
		$command  = new $commands['app:info']['class']($this->logger, $this->runner);

		$command->bomb();
		$result = CITestStreamFilter::$buffer;
		stream_filter_remove($this->error_filter);

		$this->assertStringContainsString('Invalid background color:', $result);
	}

	public function testNonexistantCommand()
	{
		// catch errors too
		$this->stream_filter = stream_filter_append(STDERR, 'CITestStreamFilter');

		$this->runner->index(['app:oops']);
		$result = CITestStreamFilter::$buffer;

		$this->assertStringContainsString('not found', $result);
	}

	public function testAbstractCommand()
	{
		// catch errors too
		$this->stream_filter = stream_filter_append(STDERR, 'CITestStreamFilter');

		$this->runner->index(['app:pablo']);
		$result = CITestStreamFilter::$buffer;

		$this->assertStringContainsString('not found', $result);
	}

	public function testNamespacesCommand()
	{
		$this->runner->index(['namespaces']);
		$result = CITestStreamFilter::$buffer;

		$this->assertStringContainsString('| Namespace', $result);
		$this->assertStringContainsString('| Config', $result);
		$this->assertStringContainsString('| Yes', $result);
	}

	public function testRoutesCommand()
	{
		$this->runner->index(['routes']);
		$result = CITestStreamFilter::$buffer;

		$this->assertStringContainsString('| Route', $result);
		$this->assertStringContainsString('| testing', $result);
		$this->assertStringContainsString('\\TestController::index', $result);
	}

}
