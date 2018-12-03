<?php namespace CodeIgniter\CLI;

use Tests\Support\MockCodeIgniter;
use Tests\Support\Config\MockCLIConfig;
use CodeIgniter\Test\Filters\CITestStreamFilter;

class ConsoleTest extends \CIUnitTestCase
{

	private $stream_filter;

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

		$this->app = new MockCodeIgniter(new MockCLIConfig());
	}

	public function tearDown()
	{
		stream_filter_remove($this->stream_filter);
	}

	public function testNew()
	{
		$console = new \CodeIgniter\CLI\Console($this->app);
		$this->assertInstanceOf(Console::class, $console);
	}

	public function testHeader()
	{
		$console = new \CodeIgniter\CLI\Console($this->app);
		$console->showHeader();
		$result = CITestStreamFilter::$buffer;
		$this->assertTrue(strpos($result, 'CodeIgniter CLI Tool') > 0);
	}

	public function testRun()
	{
		$console = new \CodeIgniter\CLI\Console($this->app);
		$console->run(true);
		$result = CITestStreamFilter::$buffer;

		// close open buffer
		ob_end_clean();

		// make sure the result looks like a command list
		$this->assertContains('Lists the available commands.', $result);
		$this->assertContains('Displays basic usage information.', $result);
	}

}
