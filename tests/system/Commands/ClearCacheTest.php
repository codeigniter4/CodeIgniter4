<?php namespace CodeIgniter\Commands;

use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\CommandRunner;
use CodeIgniter\Config\Config;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use CodeIgniter\Test\Mock\MockAppConfig;
use Config\Services;

class ClearCacheTest extends CIUnitTestCase
{
	protected $streamFilter;
	protected $result;

	protected function setUp(): void
	{
		parent::setUp();

		CITestStreamFilter::$buffer = '';
		$this->streamFilter         = stream_filter_append(STDOUT, 'CITestStreamFilter');

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
		if (! $this->result)
		{
			return;
		}

		stream_filter_remove($this->streamFilter);
	}

	public function testClearCacheInvalidHandler()
	{
		$config          = config('Cache');
		$config->handler = 'junk';
		Config::injectMock('Cache', $config);

		$this->runner->index(['cache:clear']);
		$result = CITestStreamFilter::$buffer;

		$this->assertStringContainsString('junk is not a valid cache handler.', $result);
	}
}
