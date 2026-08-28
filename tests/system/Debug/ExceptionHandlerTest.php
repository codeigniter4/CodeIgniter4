<?php

declare(strict_types=1);

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
use CodeIgniter\Exceptions\RuntimeException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\IniTestTrait;
use CodeIgniter\Test\StreamFilterTrait;
use Config\App;
use Config\Exceptions as ExceptionsConfig;
use Config\Services;
use PHPUnit\Framework\Attributes\Group;
use stdClass;

/**
 * @internal
 */
#[Group('Others')]
final class ExceptionHandlerTest extends CIUnitTestCase
{
    use StreamFilterTrait;
    use IniTestTrait;

    private ExceptionHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new ExceptionHandler(new ExceptionsConfig());

        $this->resetServices();
    }

    public function testDetermineViewsPageNotFoundException(): void
    {
        $determineView = self::getPrivateMethodInvoker($this->handler, 'determineView');

        $exception    = PageNotFoundException::forControllerNotFound('Foo', 'bar');
        $templatePath = APPPATH . 'Views/errors/html';
        $viewFile     = $determineView($exception, $templatePath);

        $this->assertSame('error_404.php', $viewFile);
    }

    public function testDetermineViewsRuntimeException(): void
    {
        $determineView = self::getPrivateMethodInvoker($this->handler, 'determineView');

        $exception    = new RuntimeException('Exception');
        $templatePath = APPPATH . 'Views/errors/html';
        $viewFile     = $determineView($exception, $templatePath);

        $this->assertSame('error_exception.php', $viewFile);
    }

    public function testDetermineViewsRuntimeExceptionCode404(): void
    {
        $determineView = self::getPrivateMethodInvoker($this->handler, 'determineView');

        $exception    = new RuntimeException('foo', 404);
        $templatePath = APPPATH . 'Views/errors/html';
        $viewFile     = $determineView($exception, $templatePath);

        $this->assertSame('error_exception.php', $viewFile);
    }

    public function testDetermineViewsDisplayErrorsOffRuntimeException(): void
    {
        ini_set('display_errors', '0');

        $determineView = self::getPrivateMethodInvoker($this->handler, 'determineView');

        $exception    = new RuntimeException('Exception');
        $templatePath = APPPATH . 'Views/errors/html';
        $viewFile     = $determineView($exception, $templatePath);

        $this->assertSame('production.php', $viewFile);

        ini_set('display_errors', '1');
    }

    public function testCollectVars(): void
    {
        $collectVars = self::getPrivateMethodInvoker($this->handler, 'collectVars');

        $vars = $collectVars(new RuntimeException('This.'), 404);

        $this->assertIsArray($vars);
        $this->assertCount(7, $vars);

        foreach (['title', 'type', 'code', 'message', 'file', 'line', 'trace'] as $key) {
            $this->assertArrayHasKey($key, $vars);
        }

        $this->assertArrayNotHasKey('copyableErrorReport', $vars);
    }

    public function testCopyErrorReportIncludesPreviousExceptions(): void
    {
        $previous  = new RuntimeException('Root cause.');
        $exception = new RuntimeException('Top level.', 0, $previous);

        $report = $this->extractCopyableErrorReport($this->renderHtmlException($exception));

        $this->assertStringContainsString('## Previous Exceptions', $report);
        $this->assertStringContainsString('* CodeIgniter\Exceptions\RuntimeException - Root cause.', $report);
    }

    public function testCopyErrorReportOmitsSensitiveRequestDataAndTraceArgs(): void
    {
        $exception = $this->createExceptionWithSensitiveTraceArgument();

        service('superglobals')
            ->setCookie('debug_cookie', 'cookie-secret')
            ->setPost('debug_post', 'post-secret');

        try {
            $report = $this->extractCopyableErrorReport($this->renderHtmlException($exception));

            $this->assertStringNotContainsString('secret-token', $report);
            $this->assertStringNotContainsString('cookie-secret', $report);
            $this->assertStringNotContainsString('post-secret', $report);
            $this->assertStringNotContainsString('$_COOKIE', $report);
            $this->assertStringNotContainsString('$_POST', $report);
        } finally {
            service('superglobals')->unsetCookie('debug_cookie')->unsetPost('debug_post');
        }
    }

    public function testCopyErrorReportOmitsQueryStringFromUrl(): void
    {
        $config  = new App();
        $secret  = 'query-secret';
        $token   = '?token=';
        $request = new IncomingRequest(
            $config,
            new SiteURI($config, '/orders?token=' . $secret, 'example.test', 'https'),
            null,
            new UserAgent(),
        );

        Services::injectMock('request', $request);

        try {
            $report = $this->extractCopyableErrorReport($this->renderHtmlException(new RuntimeException('Query test.')));

            $this->assertStringContainsString('- Path: /orders', $report);
            $this->assertStringContainsString('- URL: https://example.test/orders', $report);
            $this->assertStringNotContainsString($secret, $report);
            $this->assertStringNotContainsString($token, $report);
        } finally {
            $this->resetServices();
        }
    }

    public function testHandleWebPageNotFoundExceptionDoNotAcceptHTML(): void
    {
        $exception = PageNotFoundException::forControllerNotFound('Foo', 'bar');

        $request = service('incomingrequest', null, false);
        /** @var Response $response */
        $response = service('response', null, false);
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

        $request = service('incomingrequest', null, false);
        $request->setHeader('accept', 'text/html');
        /** @var Response $response */
        $response = service('response', null, false);
        $response->pretend();

        ob_start();
        $this->handler->handle($exception, $request, $response, 404, EXIT_ERROR);
        $output = ob_get_clean();

        $this->assertStringContainsString('<title>404 - Page Not Found</title>', (string) $output);
    }

    public function testHandleWebRuntimeExceptionAcceptHTMLIncludesCopyErrorReport(): void
    {
        $output = $this->renderHtmlException(new RuntimeException('Something went wrong.'));
        $report = $this->extractCopyableErrorReport($output);

        $this->assertStringContainsString('Copy Details', $output);
        $this->assertStringContainsString('# Something went wrong.', $report);

        foreach (['## Exception', '## Environment', '## Request', '## Source', '## Stack Trace'] as $section) {
            $this->assertStringContainsString($section, $report);
        }
    }

    public function testHandleWebRuntimeExceptionEscapesCopyErrorReport(): void
    {
        $output = $this->renderHtmlException(new RuntimeException('</textarea><script>alert(1)</script>'));

        $this->assertStringNotContainsString('</textarea><script>alert(1)</script>', $output);
        $this->assertStringContainsString('&lt;/textarea&gt;&lt;script&gt;alert(1)&lt;/script&gt;', $output);
    }

    public function testHandleCLIPageNotFoundException(): void
    {
        $exception = PageNotFoundException::forControllerNotFound('Foo', 'bar');

        $request = Services::clirequest(null, false);
        $request->setHeader('accept', 'text/html');
        /** @var Response $response */
        $response = service('response', null, false);
        $response->pretend();

        $this->handler->handle($exception, $request, $response, 404, EXIT_ERROR);

        $this->assertStringContainsString(
            'ERROR: 404',
            $this->getStreamFilterBuffer(),
        );
        $this->assertStringContainsString(
            'Controller or its method is not found: Foo::bar',
            $this->getStreamFilterBuffer(),
        );

        $this->resetStreamFilterBuffer();
    }

    public function testMaskSensitiveData(): void
    {
        $maskSensitiveData = self::getPrivateMethodInvoker($this->handler, 'maskSensitiveData');

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
                'args'     => [],
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
        $maskSensitiveData = self::getPrivateMethodInvoker($this->handler, 'maskSensitiveData');

        $trace = [
            0 => [
                'file'     => '/var/www/CodeIgniter4/app/Controllers/Home.php',
                'line'     => 15,
                'function' => 'f',
                'class'    => Home::class,
                'type'     => '->',
                'args'     => [],
            ],
            1 => [
                'file'     => '/var/www/CodeIgniter4/system/CodeIgniter.php',
                'line'     => 932,
                'function' => 'index',
                'class'    => Home::class,
                'type'     => '->',
                'args'     => [],
            ],
        ];
        $keysToMask = ['file'];
        $path       = '';

        $newTrace = $maskSensitiveData($trace, $keysToMask, $path);

        $this->assertSame('/var/www/CodeIgniter4/app/Controllers/Home.php', $newTrace[0]['file']);
    }

    public function testHighlightFile(): void
    {
        $this->backupIniValues([
            'highlight.comment', 'highlight.default', 'highlight.html', 'highlight.keyword', 'highlight.string',
        ]);

        $highlightFile = self::getPrivateMethodInvoker($this->handler, 'highlightFile');
        $result        = $highlightFile(SUPPORTPATH . 'Controllers' . DIRECTORY_SEPARATOR . 'Hello.php', 16);

        $resultFile = match (true) {
            PHP_VERSION_ID < 80300 => 'highlightFile_pre_80300.html',
            default                => 'highlightFile.html',
        };

        $expected = file_get_contents(SUPPORTPATH . 'Debug' . DIRECTORY_SEPARATOR . $resultFile);

        $this->assertSame($expected, $result);

        $this->restoreIniValues();
    }

    public function testSanitizeDataWithResource(): void
    {
        $sanitizeData = self::getPrivateMethodInvoker($this->handler, 'sanitizeData');

        // Create a resource (file handle)
        $resource = fopen('php://memory', 'rb');
        $result   = $sanitizeData($resource);

        $this->assertIsString($result);
        $this->assertStringStartsWith('[Resource #', $result);
        $this->assertStringEndsWith(']', $result);

        fclose($resource);
    }

    public function testSanitizeDataWithClosure(): void
    {
        $sanitizeData = self::getPrivateMethodInvoker($this->handler, 'sanitizeData');

        $closure = static fn (): string => 'test';
        $result  = $sanitizeData($closure);

        $this->assertSame('[Closure]', $result);
    }

    public function testSanitizeDataWithCircularReference(): void
    {
        $sanitizeData = self::getPrivateMethodInvoker($this->handler, 'sanitizeData');

        // Create an object with circular reference
        $obj       = new stdClass();
        $obj->self = $obj;

        $result = $sanitizeData($obj);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('self', $result);
        $this->assertStringContainsString('*RECURSION*', (string) $result['self']);
        $this->assertStringContainsString('stdClass', (string) $result['self']);
    }

    public function testSanitizeDataWithArrayContainingResource(): void
    {
        $sanitizeData = self::getPrivateMethodInvoker($this->handler, 'sanitizeData');

        $resource = fopen('php://memory', 'rb');
        $data     = [
            'string'   => 'test',
            'number'   => 123,
            'resource' => $resource,
        ];

        $result = $sanitizeData($data);

        $this->assertIsArray($result);
        $this->assertSame('test', $result['string']);
        $this->assertSame(123, $result['number']);
        $this->assertIsString($result['resource']);
        $this->assertStringStartsWith('[Resource #', $result['resource']);

        fclose($resource);
    }

    public function testSanitizeDataWithObjectContainingResource(): void
    {
        $sanitizeData = self::getPrivateMethodInvoker($this->handler, 'sanitizeData');

        $resource = fopen('php://memory', 'rb');

        $obj           = new stdClass();
        $obj->name     = 'test';
        $obj->connID   = $resource;
        $obj->database = 'mydb';

        $result = $sanitizeData($obj);

        $this->assertIsArray($result);
        $this->assertSame('test', $result['name']);
        $this->assertSame('mydb', $result['database']);
        $this->assertIsString($result['connID']);
        $this->assertStringStartsWith('[Resource #', $result['connID']);

        fclose($resource);
    }

    public function testSanitizeDataWithNestedObjects(): void
    {
        $sanitizeData = self::getPrivateMethodInvoker($this->handler, 'sanitizeData');

        $resource = fopen('php://memory', 'rb');

        $inner         = new stdClass();
        $inner->connID = $resource;
        $inner->host   = 'localhost';

        $outer        = new stdClass();
        $outer->db    = $inner;
        $outer->cache = 'file';

        $result = $sanitizeData($outer);

        $this->assertIsArray($result);
        $this->assertSame('file', $result['cache']);
        $this->assertIsArray($result['db']);
        $this->assertSame('localhost', $result['db']['host']);
        $this->assertIsString($result['db']['connID']);
        $this->assertStringStartsWith('[Resource #', $result['db']['connID']);

        fclose($resource);
    }

    public function testSanitizeDataWithScalars(): void
    {
        $sanitizeData = self::getPrivateMethodInvoker($this->handler, 'sanitizeData');

        $this->assertSame('string', $sanitizeData('string'));
        $this->assertSame(123, $sanitizeData(123));
        $this->assertEqualsWithDelta(45.67, $sanitizeData(45.67), PHP_FLOAT_EPSILON);
        $this->assertTrue($sanitizeData(true));
        $this->assertFalse($sanitizeData(false));
        $this->assertNull($sanitizeData(null));
    }

    private function createExceptionWithSensitiveTraceArgument(): RuntimeException
    {
        return new RuntimeException('Trace argument test.');
    }

    private function extractCopyableErrorReport(string $output): string
    {
        $this->assertSame(1, preg_match('#<textarea[^>]*>\K.*?(?=</textarea>)#s', $output, $matches));

        return html_entity_decode($matches[0], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function renderHtmlException(RuntimeException $exception): string
    {
        $this->backupIniValues([
            'highlight.comment', 'highlight.default', 'highlight.html', 'highlight.keyword', 'highlight.string',
        ]);

        $render = self::getPrivateMethodInvoker($this->handler, 'render');

        ob_start();

        try {
            $render($exception, 500, APPPATH . 'Views/errors/html/error_exception.php');

            return ob_get_clean();
        } finally {
            $this->restoreIniValues();
        }
    }
}
