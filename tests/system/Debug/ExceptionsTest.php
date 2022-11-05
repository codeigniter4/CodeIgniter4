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
use ErrorException;
use RuntimeException;

/**
 * @internal
 *
 * @group Others
 */
final class ExceptionsTest extends CIUnitTestCase
{
    use ReflectionHelper;

    private \CodeIgniter\Debug\Exceptions $exception;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        unset($_SERVER['CODEIGNITER_SCREAM_DEPRECATIONS']);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $_SERVER['CODEIGNITER_SCREAM_DEPRECATIONS'] = '1';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->exception = new Exceptions(new ExceptionsConfig(), Services::request(), Services::response());
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testDeprecationsOnPhp81DoNotThrow(): void
    {
        $config = new ExceptionsConfig();

        $config->logDeprecations     = true;
        $config->deprecationLogLevel = 'error';

        $this->exception = new Exceptions($config, Services::request(), Services::response());
        $this->exception->initialize();

        // this is only needed for IDEs not to complain that strlen does not accept explicit null
        $maybeNull = PHP_VERSION_ID >= 80100 ? null : 'random string';

        try {
            strlen($maybeNull);
            $this->assertLogContains('error', '[DEPRECATED] strlen(): ');
        } catch (ErrorException $e) {
            $this->fail('The catch block should not be reached.');
        } finally {
            restore_error_handler();
            restore_exception_handler();
        }
    }

    public function testSuppressedDeprecationsAreLogged(): void
    {
        $config = new ExceptionsConfig();

        $config->logDeprecations     = true;
        $config->deprecationLogLevel = 'error';

        $this->exception = new Exceptions($config, Services::request(), Services::response());
        $this->exception->initialize();

        @trigger_error('Hello! I am a deprecation!', E_USER_DEPRECATED);
        $this->assertLogContains('error', '[DEPRECATED] Hello! I am a deprecation!');

        restore_error_handler();
        restore_exception_handler();
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

        $this->assertSame([500, EXIT_ERROR], $determineCodes(new RuntimeException('This.')));
        $this->assertSame([500, EXIT_ERROR], $determineCodes(new RuntimeException('That.', 600)));
        $this->assertSame([500, EXIT_ERROR], $determineCodes(new RuntimeException('There.', 404)));
        $this->assertSame([500, EXIT_ERROR], $determineCodes(new RuntimeException('This.', 167)));
        $this->assertSame([500, EXIT_CONFIG], $determineCodes(new ConfigException('This.')));
        $this->assertSame([500, EXIT_CONFIG], $determineCodes(new CastException('This.')));
        $this->assertSame([500, EXIT_DATABASE], $determineCodes(new DatabaseException('This.')));
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
