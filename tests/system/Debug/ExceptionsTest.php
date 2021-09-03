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

    /**
     * @var Exceptions
     */
    private $exception;

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

        $this->assertSame([500, 9], $determineCodes(new RuntimeException('This.')));
        $this->assertSame([500, 1], $determineCodes(new RuntimeException('That.', 600)));
        $this->assertSame([404, 1], $determineCodes(new RuntimeException('There.', 404)));
    }

    /**
     * @dataProvider dirtyPathsProvider
     */
    public function testCleanPaths(string $file, string $expected): void
    {
        $this->assertSame($expected, Exceptions::cleanPath($file));
    }

    public function dirtyPathsProvider()
    {
        $ds = DIRECTORY_SEPARATOR;

        yield from [
            [
                APPPATH . 'Config' . $ds . 'App.php',
                'APPPATH' . $ds . 'Config' . $ds . 'App.php',
            ],
            [
                SYSTEMPATH . 'CodeIgniter.php',
                'SYSTEMPATH' . $ds . 'CodeIgniter.php',
            ],
            [
                VENDORPATH . 'autoload.php',
                'VENDORPATH' . $ds . 'autoload.php',
            ],
            [
                FCPATH . 'index.php',
                'FCPATH' . $ds . 'index.php',
            ],
        ];
    }
}
