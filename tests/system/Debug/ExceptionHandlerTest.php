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
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Exceptions as ExceptionsConfig;
use Config\Services;
use RuntimeException;

/**
 * @internal
 *
 * @group Others
 */
final class ExceptionHandlerTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private ExceptionHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new ExceptionHandler(new ExceptionsConfig());
    }

    public function testDetermineViewsPageNotFoundException(): void
    {
        $determineView = $this->getPrivateMethodInvoker($this->handler, 'determineView');

        $exception    = PageNotFoundException::forControllerNotFound('Foo', 'bar');
        $templatePath = APPPATH . 'Views/errors/html';
        $viewFile     = $determineView($exception, $templatePath);

        $this->assertSame('error_404.php', $viewFile);
    }

    public function testDetermineViewsRuntimeException(): void
    {
        $determineView = $this->getPrivateMethodInvoker($this->handler, 'determineView');

        $exception    = new RuntimeException('Exception');
        $templatePath = APPPATH . 'Views/errors/html';
        $viewFile     = $determineView($exception, $templatePath);

        $this->assertSame('error_exception.php', $viewFile);
    }

    public function testDetermineViewsRuntimeExceptionCode404(): void
    {
        $determineView = $this->getPrivateMethodInvoker($this->handler, 'determineView');

        $exception    = new RuntimeException('foo', 404);
        $templatePath = APPPATH . 'Views/errors/html';
        $viewFile     = $determineView($exception, $templatePath);

        $this->assertSame('error_404.php', $viewFile);
    }

    public function testCollectVars(): void
    {
        $collectVars = $this->getPrivateMethodInvoker($this->handler, 'collectVars');

        $vars = $collectVars(new RuntimeException('This.'), 404);

        $this->assertIsArray($vars);
        $this->assertCount(7, $vars);

        foreach (['title', 'type', 'code', 'message', 'file', 'line', 'trace'] as $key) {
            $this->assertArrayHasKey($key, $vars);
        }
    }

    public function testHandleWebPageNotFoundExceptionDoNotAcceptHTML(): void
    {
        $exception = PageNotFoundException::forControllerNotFound('Foo', 'bar');

        $request  = Services::incomingrequest(null, false);
        $response = Services::response(null, false);
        $response->pretend();

        ob_start();
        $this->handler->handle($exception, $request, $response, 404, EXIT_ERROR);
        $output = ob_get_clean();

        $json = json_decode($output);
        $this->assertSame(PageNotFoundException::class, $json->title);
        $this->assertSame(PageNotFoundException::class, $json->type);
        $this->assertSame(404, $json->code);
        $this->assertSame('Controller or its method is not found: Foo::bar', $json->message);
    }

    public function testHandleWebPageNotFoundExceptionAcceptHTML(): void
    {
        $exception = PageNotFoundException::forControllerNotFound('Foo', 'bar');

        $request = Services::incomingrequest(null, false);
        $request->setHeader('accept', 'text/html');
        $response = Services::response(null, false);
        $response->pretend();

        ob_start();
        $this->handler->handle($exception, $request, $response, 404, EXIT_ERROR);
        $output = ob_get_clean();

        $this->assertStringContainsString('<title>404 - Page Not Found</title>', $output);
    }

    public function testHandleCLIPageNotFoundException(): void
    {
        $exception = PageNotFoundException::forControllerNotFound('Foo', 'bar');

        $request = Services::clirequest(null, false);
        $request->setHeader('accept', 'text/html');
        $response = Services::response(null, false);
        $response->pretend();

        $this->handler->handle($exception, $request, $response, 404, EXIT_ERROR);

        $this->assertStringContainsString(
            'ERROR: 404',
            $this->getStreamFilterBuffer()
        );
        $this->assertStringContainsString(
            'Controller or its method is not found: Foo::bar',
            $this->getStreamFilterBuffer()
        );

        $this->resetStreamFilterBuffer();
    }

    public function testMaskSensitiveData(): void
    {
        $maskSensitiveData = $this->getPrivateMethodInvoker($this->handler, 'maskSensitiveData');

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
        $maskSensitiveData = $this->getPrivateMethodInvoker($this->handler, 'maskSensitiveData');

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
