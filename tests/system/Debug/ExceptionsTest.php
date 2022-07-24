<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Debug;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Entity\Exceptions\CastException;
use CodeIgniter\Exceptions\ConfigException;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ReflectionHelper;
use Config\Exceptions as ExceptionsConfig;
use Config\Services;
use RuntimeException;

/**
 * @internal
 */
final class ExceptionsTest extends CIUnitTestCase
{
    use ReflectionHelper;

    private \CodeIgniter\Debug\Exceptions $exception;

    protected function setUp(): void
    {
        $this->exception = new Exceptions(new ExceptionsConfig(), Services::request(), Services::response());
    }

    public function testDetermineViews(): void
    {
        $determineView = $this->getPrivateMethodInvoker($this->exception, 'determineView');

        $this->assertSame('error_404.php', $determineView(PageNotFoundException::forControllerNotFound('Foo', 'bar'), ''));
        $this->assertSame('error_exception.php', $determineView(new RuntimeException('Exception'), ''));
        $this->assertSame('error_404.php', $determineView(new RuntimeException('foo', 404), 'app/Views/errors/cli'));
    }

    public function testCollectVars(): void
    {
        $vars = $this->getPrivateMethodInvoker($this->exception, 'collectVars')(new RuntimeException('This.'), 404);

        $this->assertIsArray($vars);
        $this->assertCount(7, $vars);

        foreach (['title', 'type', 'code', 'message', 'file', 'line', 'trace'] as $key) {
            $this->assertArrayHasKey($key, $vars);
        }
    }

    public function testDetermineCodes(): void
    {
        $determineCodes = $this->getPrivateMethodInvoker($this->exception, 'determineCodes');

        $this->assertSame([500, EXIT__AUTO_MIN], $determineCodes(new RuntimeException('This.')));
        $this->assertSame([500, EXIT_ERROR], $determineCodes(new RuntimeException('That.', 600)));
        $this->assertSame([404, EXIT_ERROR], $determineCodes(new RuntimeException('There.', 404)));
        $this->assertSame([167, EXIT_ERROR], $determineCodes(new RuntimeException('This.', 167)));
        // @TODO This exit code should be EXIT_CONFIG.
        $this->assertSame([500, 12], $determineCodes(new ConfigException('This.')));
        // @TODO This exit code should be EXIT_CONFIG.
        $this->assertSame([500, 9], $determineCodes(new CastException('This.')));
        // @TODO This exit code should be EXIT_DATABASE.
        $this->assertSame([500, 17], $determineCodes(new DatabaseException('This.')));
    }

    public function testRenderBacktrace(): void
    {
        $renderer  = self::getPrivateMethodInvoker(Exceptions::class, 'renderBacktrace');
        $exception = new RuntimeException('This.');

        $renderedBacktrace = $renderer($exception->getTrace());
        $renderedBacktrace = explode("\n", $renderedBacktrace);

        foreach ($renderedBacktrace as $trace) {
            $this->assertMatchesRegularExpression(
                '/^\s*\d* .+(?:\(\d+\))?: \S+(?:(?:\->|::)\S+)?\(.*\)$/',
                $trace
            );
        }
    }
}
