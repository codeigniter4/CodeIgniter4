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

use App\Controllers\Home;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Entity\Exceptions\CastException;
use CodeIgniter\Exceptions\ConfigException;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ReflectionHelper;
use Config\Exceptions as ExceptionsConfig;
use ErrorException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhp;
use RuntimeException;

/**
 * @internal
 */
#[Group('Others')]
final class ExceptionsTest extends CIUnitTestCase
{
    use ReflectionHelper;

    private Exceptions $exception;

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

        $this->exception = new Exceptions(new ExceptionsConfig());
    }

    #[RequiresPhp('>= 8.1')]
    public function testDeprecationsOnPhp81DoNotThrow(): void
    {
        $config = new ExceptionsConfig();

        $config->logDeprecations     = true;
        $config->deprecationLogLevel = 'error';

        $this->exception = new Exceptions($config);
        $this->exception->initialize();

        try {
            $result = str_contains('foobar', null); // @phpstan-ignore argument.type (Needed for testing)
            $this->assertLogContains('error', '[DEPRECATED] str_contains(): ');
        } catch (ErrorException) {
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

        $this->exception = new Exceptions($config);
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
        $this->assertSame([500, EXIT_CONFIG], $determineCodes(CastException::forInvalidInterface('This.')));
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
                $trace,
            );
        }
    }

    public function testMaskSensitiveData(): void
    {
        $maskSensitiveData = $this->getPrivateMethodInvoker($this->exception, 'maskSensitiveData');

        $trace = [
            0 => [
                'file'     => '/var/www/CodeIgniter4/app/Controllers/Home.php',
                'line'     => 15,
                'function' => 'f',
                'class'    => Home::class,
                'type'     => '->',
                'args'     => [
                    0 => (object) [
                        'password' => 'secret1',
                    ],
                    1 => (object) [
                        'default' => [
                            'password' => 'secret2',
                        ],
                    ],
                    2 => [
                        'password' => 'secret3',
                    ],
                    3 => [
                        'default' => ['password' => 'secret4'],
                    ],
                ],
            ],
            1 => [
                'file'     => '/var/www/CodeIgniter4/system/CodeIgniter.php',
                'line'     => 932,
                'function' => 'index',
                'class'    => Home::class,
                'type'     => '->',
                'args'     => [
                ],
            ],
        ];
        $keysToMask = ['password'];
        $path       = '';

        $newTrace = $maskSensitiveData($trace, $keysToMask, $path);

        $this->assertSame(['password' => '******************'], (array) $newTrace[0]['args'][0]);
        $this->assertSame(['password' => '******************'], $newTrace[0]['args'][1]->default);
        $this->assertSame(['password' => '******************'], $newTrace[0]['args'][2]);
        $this->assertSame(['password' => '******************'], $newTrace[0]['args'][3]['default']);
    }

    public function testMaskSensitiveDataTraceDataKey(): void
    {
        $maskSensitiveData = $this->getPrivateMethodInvoker($this->exception, 'maskSensitiveData');

        $trace = [
            0 => [
                'file'     => '/var/www/CodeIgniter4/app/Controllers/Home.php',
                'line'     => 15,
                'function' => 'f',
                'class'    => Home::class,
                'type'     => '->',
                'args'     => [
                ],
            ],
            1 => [
                'file'     => '/var/www/CodeIgniter4/system/CodeIgniter.php',
                'line'     => 932,
                'function' => 'index',
                'class'    => Home::class,
                'type'     => '->',
                'args'     => [
                ],
            ],
        ];
        $keysToMask = ['file'];
        $path       = '';

        $newTrace = $maskSensitiveData($trace, $keysToMask, $path);

        $this->assertSame('/var/www/CodeIgniter4/app/Controllers/Home.php', $newTrace[0]['file']);
    }
}
