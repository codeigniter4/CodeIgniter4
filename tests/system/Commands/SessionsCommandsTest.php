<?php namespace CodeIgniter\Commands;

use Config\Services;
use CodeIgniter\Test\Mock\MockAppConfig;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\CommandRunner;
use CodeIgniter\Test\Filters\CITestStreamFilter;

class SessionsCommandsTest extends \CodeIgniter\Test\CIUnitTestCase
{
	private $stream_filter;
	protected $env;
	protected $config;
	protected $request;
	protected $response;
	protected $logger;
	protected $runner;
	private $result;

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

		$result = remove_invisible_characters($this->result);
		$result = str_replace('[0;32m', '', $result);
		$result = str_replace('[0m', '', $result);
		$file   = trim(substr($result, 14));
		$file   = str_replace('APPPATH', APPPATH, $file);

		unlink($file);
	}

	public function testCreateMigrationCommand()
	{
		$this->runner->index(['session:migration']);
		$result = CITestStreamFilter::$buffer;

		// make sure we end up with a migration class in the right place
		// or at least that we claim to have done so
		// separate assertions avoid console color codes
		$this->assertStringContainsString('Created file:', $result);
		$this->assertStringContainsString('APPPATH/Database/Migrations/', $result);
		$this->assertStringContainsString('_create_ci_sessions_table.php', $result);

		$this->result = $result;
	}

	public function testOverriddenCreateMigrationCommand()
	{
		$_SERVER['argv'] = [
			'spark',
			'session:migration',
			'-t',
			'mygoodies',
		];
		$_SERVER['argc'] = 4;
		CLI::init();

		$this->runner->index(['session:migration']);
		$result = CITestStreamFilter::$buffer;

		// make sure we end up with a migration class in the right place
		$this->assertStringContainsString('Created file:', $result);
		$this->assertStringContainsString('APPPATH/Database/Migrations/', $result);
		$this->assertStringContainsString('_create_mygoodies_table.php', $result);

		$this->result = $result;
	}

}
