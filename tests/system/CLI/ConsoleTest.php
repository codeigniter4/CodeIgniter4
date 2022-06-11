<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\CLI;

use CodeIgniter\CodeIgniter;
use CodeIgniter\Config\DotEnv;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCLIConfig;
use CodeIgniter\Test\Mock\MockCodeIgniter;
use CodeIgniter\Test\StreamFilterTrait;

/**
 * @internal
 */
final class ConsoleTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerStreamFilterClass()->appendOutputStreamFilter();

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
        $this->app->setContext('spark');
    }

    protected function tearDown(): void
    {
        $this->removeOutputStreamFilter();
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
        $this->assertGreaterThan(
            0,
            strpos(
                $this->getStreamFilterBuffer(),
                sprintf('CodeIgniter v%s Command Line Tool', CodeIgniter::CI_VERSION)
            )
        );
    }

    public function testNoHeader()
    {
        $console = new Console($this->app);
        $console->showHeader(true);
        $this->assertSame('', $this->getStreamFilterBuffer());
    }

    public function testRun()
    {
        $request = new CLIRequest(config('App'));
        $this->app->setRequest($request);

        $console = new Console($this->app);
        $console->run(true);

        // close open buffer
        ob_end_clean();

        // make sure the result looks like a command list
        $this->assertStringContainsString('Lists the available commands.', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('Displays basic usage information.', $this->getStreamFilterBuffer());
    }
}
