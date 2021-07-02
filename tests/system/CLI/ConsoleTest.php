<?php

namespace CodeIgniter\CLI;

use CodeIgniter\CodeIgniter;
use CodeIgniter\Config\DotEnv;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use CodeIgniter\Test\Mock\MockCLIConfig;
use CodeIgniter\Test\Mock\MockCodeIgniter;

/**
 * @internal
 */
final class ConsoleTest extends CIUnitTestCase
{
    private $stream_filter;

    protected function setUp(): void
    {
        parent::setUp();

        CITestStreamFilter::$buffer = '';
        $this->stream_filter        = stream_filter_append(STDOUT, 'CITestStreamFilter');

        $this->env = new DotEnv(ROOTPATH);
        $this->env->load();

        // Set environment values that would otherwise stop the framework from functioning during tests.
        if (! isset($_SERVER['app.baseURL'])) {
            $_SERVER['app.baseURL'] = 'http://example.com/';
        }

        $_SERVER['argv'] = [
            'spark',
            'list',
        ];
        $_SERVER['argc'] = 2;
        CLI::init();

        $this->app = new MockCodeIgniter(new MockCLIConfig());
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->stream_filter);
    }

    public function testNew()
    {
        $console = new Console($this->app);
        $this->assertInstanceOf(Console::class, $console);
    }

    public function testHeader()
    {
        $console = new Console($this->app);
        $console->showHeader();
        $result = CITestStreamFilter::$buffer;
        $this->assertTrue(strpos($result, sprintf('CodeIgniter v%s Command Line Tool', CodeIgniter::CI_VERSION)) > 0);
    }

    public function testNoHeader()
    {
        $console = new Console($this->app);
        $console->showHeader(true);
        $result = CITestStreamFilter::$buffer;
        $this->assertSame('', $result);
    }

    public function testRun()
    {
        $request = new CLIRequest(config('App'));
        $this->app->setRequest($request);

        $console = new Console($this->app);
        $console->run(true);
        $result = CITestStreamFilter::$buffer;

        // close open buffer
        ob_end_clean();

        // make sure the result looks like a command list
        $this->assertStringContainsString('Lists the available commands.', $result);
        $this->assertStringContainsString('Displays basic usage information.', $result);
    }
}
