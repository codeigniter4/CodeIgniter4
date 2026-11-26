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
use CodeIgniter\Debug\Inspection\CodeSnippet;
use CodeIgniter\Debug\Inspection\Frames;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Exceptions\RuntimeException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use CodeIgniter\Test\IniTestTrait;
use CodeIgniter\Test\StreamFilterTrait;
use CodeIgniter\Test\TestLogger;
use Config\App;
use Config\Exceptions as ExceptionsConfig;
use Config\Services;
use Kint\Renderer\AbstractRenderer;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use ReflectionProperty;
use SensitiveParameter;
use SensitiveParameterValue;

/**
 * @internal
 */
#[Group('Others')]
final class ExceptionHandlerTest extends CIUnitTestCase
{
    use StreamFilterTrait;
    use IniTestTrait;

    private ExceptionHandler $handler;

    #[Override]
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
        $this->assertStringContainsString('- [CodeIgniter\Exceptions\RuntimeException] Root cause.', $report);
        $this->assertStringContainsString('()->testCopyErrorReportIncludesPreviousExceptions()', $report);
    }

    public function testCopyErrorReportOmitsSensitiveRequestDataAndTraceArgs(): void
    {
        $this->backupIniValues(['zend.exception_ignore_args']);
        ini_set('zend.exception_ignore_args', '0');

        $argument  = bin2hex(random_bytes(12));
        $cookie    = bin2hex(random_bytes(12));
        $post      = bin2hex(random_bytes(12));
        $exception = $this->forwardSensitiveTraceArgument(password: $argument);

        service('superglobals')
            ->setCookie(config('Session')->cookieName, $cookie)
            ->setPost('password', $post);

        try {
            $this->assertSame($argument, $exception->getTrace()[0]['args'][0]);

            $output = $this->renderHtmlException($exception);
            $report = $this->extractCopyableErrorReport($output);

            $this->assertStringContainsString('<summary>Arguments</summary>', $output);
            $this->assertStringNotContainsString($argument, $output);
            $this->assertStringNotContainsString($argument, $report);
            $this->assertStringNotContainsString($cookie, $output);
            $this->assertStringNotContainsString($post, $output);
            $this->assertStringNotContainsString('$_COOKIE', $report);
            $this->assertStringNotContainsString('$_POST', $report);
        } finally {
            $this->restoreIniValues();
            service('superglobals')->unsetCookie(config('Session')->cookieName)->unsetPost('password');
        }
    }

    public function testArgumentPreviewMatchesTheNamedFunction(): void
    {
        $this->backupIniValues(['zend.exception_ignore_args']);
        ini_set('zend.exception_ignore_args', '0');

        try {
            $argument  = bin2hex(random_bytes(12));
            $exception = $this->createExceptionForArgumentPreview($argument);
            $output    = $this->renderHtmlException($exception);

            $this->assertSame(1, preg_match('#<article[^>]+id="frame-0001".*?</article>#s', $output, $panel));
            $this->assertStringContainsString('<dt><code><b>$sample</b></code></dt>', $panel[0]);
            $this->assertStringContainsString($argument, $panel[0]);
            $this->assertStringNotContainsString('<dt><code><b>$inner</b></code></dt>', $panel[0]);

            $this->assertSame(1, preg_match('#<article[^>]+id="frame-0000".*?</article>#s', $output, $firstPanel));
            $this->assertStringContainsString('<dt><code><b>$inner</b></code></dt>', $firstPanel[0]);
            $this->assertStringContainsString('value passed to the inner call', $firstPanel[0]);
        } finally {
            $this->restoreIniValues();
        }
    }

    public function testDetailedHtmlRendersRecursiveArgumentsWithRedactionConfigured(): void
    {
        $this->backupIniValues(['zend.exception_ignore_args']);
        ini_set('zend.exception_ignore_args', '0');

        $config                       = new ExceptionsConfig();
        $config->sensitiveDataInTrace = ['pin'];
        $this->handler                = new ExceptionHandler($config);
        $secret                       = bin2hex(random_bytes(12));
        $argument                     = ['pin' => $secret];
        $argument['self']             = &$argument;

        try {
            $error  = (static fn (array $data): RuntimeException => new RuntimeException('Recursive trace test.'))($argument);
            $output = $this->renderHtmlException($error);

            $this->assertStringContainsString('*RECURSION*', $output);
            $this->assertStringNotContainsString($secret, $output);
        } finally {
            $this->restoreIniValues();
        }
    }

    #[DataProvider('provideWebErrorRespectsDisabledBacktraces')]
    public function testWebErrorRespectsDisabledBacktraces(string $accept): void
    {
        $homePath   = var_export(HOMEPATH, true);
        $bootstrap  = var_export(SYSTEMPATH . 'util_bootstrap.php', true);
        $mediaType  = var_export($accept, true);
        $sourceFile = var_export(SUPPORTPATH . 'Controllers/Hello.php', true);

        $code = <<<PHP
            chdir({$homePath});
            define('ENVIRONMENT', 'development');
            define('SHOW_DEBUG_BACKTRACE', false);
            require {$bootstrap};
            ini_set('display_errors', '1');

            use CodeIgniter\\Boot;
            use CodeIgniter\\Debug\\ExceptionHandler;
            use Config\\Exceptions as ExceptionsConfig;

            (new ReflectionProperty(Boot::class, 'workerMode'))->setValue(null, true);

            function createExceptionWithHiddenTrace(): RuntimeException
            {
                \$exception = new RuntimeException('Hidden trace regression test.');
                (new ReflectionProperty(Exception::class, 'file'))->setValue(\$exception, {$sourceFile});
                (new ReflectionProperty(Exception::class, 'line'))->setValue(\$exception, 16);

                return \$exception;
            }

            \$request = service('incomingrequest', null, false);
            \$request->setHeader('accept', {$mediaType});
            (new ExceptionHandler(new ExceptionsConfig()))->handle(
                createExceptionWithHiddenTrace(),
                \$request,
                service('response'),
                500,
                EXIT_ERROR,
            );
            PHP;

        $cmd = sprintf('%s -r %s 2>&1', escapeshellarg(PHP_BINARY), escapeshellarg($code));
        exec($cmd, $lines, $status);
        $output = implode(PHP_EOL, $lines);

        $this->assertSame(0, $status, $output);
        $this->assertStringContainsString('Hidden trace regression test.', $output);
        $this->assertStringNotContainsString('createExceptionWithHiddenTrace', $output);

        if ($accept === 'text/html') {
            $report = $this->extractCopyableErrorReport($output);
            $this->assertStringNotContainsString('## Stack Trace', $report);
            $this->assertStringContainsString('## Source Excerpt', $report);
            $this->assertStringContainsString('> 16 | use CodeIgniter\Controller;', $report);
        } else {
            $this->assertSame([], json_decode($output, true, flags: JSON_THROW_ON_ERROR)['trace']);
        }
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideWebErrorRespectsDisabledBacktraces(): iterable
    {
        yield 'HTML' => ['text/html'];

        yield 'JSON' => ['application/json'];
    }

    #[DataProvider('provideApiTraceContainsFrameMetadataWithoutArguments')]
    public function testApiTraceContainsFrameMetadataWithoutArguments(string $format): void
    {
        $this->backupIniValues(['zend.exception_ignore_args']);
        ini_set('zend.exception_ignore_args', '0');

        try {
            $secret = bin2hex(random_bytes(12));
            $public = bin2hex(random_bytes(12));
            $error  = $this->exceptionWithApiArguments($secret, $public);

            $accept  = sprintf('application/%s', $format);
            $request = service('incomingrequest', null, false);
            $request->setHeader('Accept', $accept);
            $response = new Response();
            $response->pretend();
            (new ReflectionProperty($this->handler, 'format'))->setValue($this->handler, $format);

            ob_start();

            try {
                $this->handler->handle($error, $request, $response, 500, EXIT_ERROR);
                $body = ob_get_contents();
            } finally {
                ob_end_clean();
            }

            $this->assertStringContainsString($accept, $response->getHeaderLine('Content-Type'));
            $this->assertIsString($body);
            $this->assertStringContainsString('exceptionWithApiArguments', $body);
            $this->assertStringContainsString('function', $body);
            $this->assertStringNotContainsString('args', $body);
            $this->assertStringNotContainsString($secret, $body);
            $this->assertStringNotContainsString($public, $body);
        } finally {
            $this->restoreIniValues();
        }
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideApiTraceContainsFrameMetadataWithoutArguments(): iterable
    {
        yield 'JSON' => ['json'];

        yield 'XML' => ['xml'];
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
        $this->assertSame($exception->getFile(), $json->trace[0]->file);
        $this->assertSame($exception->getLine(), $json->trace[0]->line);
        $this->assertSame('forControllerNotFound', $json->trace[0]->function);
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
        $this->assertStringContainsString('# [CodeIgniter\Exceptions\RuntimeException] Something went wrong.', $report);

        foreach (['## Exception', '## Environment', '## Request', '## Stack Trace'] as $section) {
            $this->assertStringContainsString($section, $report);
        }

        $this->assertStringContainsString('- Source: in ' . Backtrace::relativePath(__FILE__) . ' (line ', $report);
    }

    public function testDetailedHtmlLinksToSourceForEachException(): void
    {
        $cause  = new RuntimeException('Root cause.');
        $output = $this->renderHtmlException(new RuntimeException('Top level.', 0, $cause));

        $this->assertStringContainsString('href="#frame-0000"', $output);
        $this->assertStringContainsString('id="frame-0000"', $output);
        $this->assertStringContainsString('href="#frame-0100"', $output);
        $this->assertStringContainsString('id="frame-0100"', $output);
        $this->assertStringContainsString('class="source-line current-line"', $output);
        $this->assertStringNotContainsString('class="stack-overflow"', $output);
        $report = $this->extractCopyableErrorReport($output);
        $this->assertSame(1, substr_count($report, '## Source Excerpt'));
        $this->assertStringContainsString("\n```php\n", $report);
        $this->assertStringContainsString(sprintf('> %d |', $cause->getLine()), $report);
        $this->assertStringNotContainsString(sprintf('> %d |', $cause->getLine() + 1), $report);
    }

    public function testDetailedHtmlCountsHiddenFramesForEachTruncatedTrace(): void
    {
        $cause     = $this->exceptionWithTraceDepth(25);
        $exception = $this->exceptionWithTraceDepth(30, $cause);
        $output    = $this->renderHtmlException($exception);

        $this->assertStringContainsString('id="frame-0019"', $output);
        $this->assertStringContainsString('id="frame-0119"', $output);
        $this->assertStringContainsString('class="stack-overflow"><b>+' . (count($exception->getTrace()) - 19) . ' hidden frames', $output);
        $this->assertStringContainsString('class="stack-overflow"><b>+' . (count($cause->getTrace()) - 19) . ' hidden frames', $output);
        $this->assertStringNotContainsString('id="frame-0020"', $output);
    }

    public function testDetailedHtmlCountsHiddenExceptions(): void
    {
        $exception = new RuntimeException('Root cause.');

        for ($index = 0; $index < 10; $index++) {
            $exception = new RuntimeException('Wrapper.', previous: $exception);
        }

        $output = $this->renderHtmlException($exception);

        $this->assertStringContainsString('id="frame-0900"', $output);
        $this->assertStringContainsString('class="stack-overflow"><b>+1 hidden exception</b></p>', $output);
    }

    public function testDetailedHtmlLinksToTheFullSourcePath(): void
    {
        $exception                          = new RuntimeException('Editor link test.');
        $format                             = AbstractRenderer::$file_link_format;
        AbstractRenderer::$file_link_format = null;

        try {
            $output = $this->renderHtmlException($exception);
        } finally {
            AbstractRenderer::$file_link_format = $format;
        }

        $this->assertSame(1, preg_match('#<p class="throw-site">in <a class="editor-link" href="([^"]+)">#', $output, $link));

        $path = str_replace('\\', '/', realpath(__FILE__));
        $url  = html_entity_decode($link[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $this->assertSame('file:///' . ltrim($path, '/'), rawurldecode($url));
        $this->assertStringContainsString('<strong>ExceptionHandlerTest.php</strong></a> (line ' . $exception->getLine() . ')', $output);
        $this->assertMatchesRegularExpression('#<span class="frame-location">.*<strong>ExceptionHandlerTest\.php</strong></a>:' . $exception->getLine() . '</span>#', $output);
    }

    public function testDetailedHtmlUsesKintEditorLinkFormat(): void
    {
        $exception                          = new RuntimeException('Configured editor link test.');
        $format                             = AbstractRenderer::$file_link_format;
        AbstractRenderer::$file_link_format = 'vscode://file%f:%l';

        try {
            $output = $this->renderHtmlException($exception);
        } finally {
            AbstractRenderer::$file_link_format = $format;
        }

        $this->assertSame(1, preg_match('#<p class="throw-site">in <a class="editor-link" href="([^"]+)">#', $output, $link));
        $url  = html_entity_decode($link[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $path = str_replace('\\', '/', realpath(__FILE__));

        $this->assertSame(sprintf('vscode://file/%s:%d', ltrim($path, '/'), $exception->getLine()), rawurldecode($url));
    }

    public function testSourceSnippetHighlightsPhpAndEscapesSource(): void
    {
        $file  = SUPPORTPATH . 'Controllers/Hello.php';
        $lines = CodeSnippet::get($file, 22);

        $this->assertStringContainsString('<span class="php-keyword">return</span>', $lines[22]);
        $this->assertStringContainsString('<span class="php-string">&#039;Hello&#039;</span>', $lines[22]);
        $this->assertStringContainsString('<span class="php-comment">', CodeSnippet::get($file, 8)[8]);
        $this->assertStringContainsString('&lt;?php', CodeSnippet::get($file, 1)[1]);
    }

    public function testDetailedHtmlShowsDiagnosticsWithMaskedValues(): void
    {
        $config                       = new ExceptionsConfig();
        $config->sensitiveDataInTrace = ['filter/internal', 'x-internal-key'];
        $config->diagnosticValues     = ['query' => ['filter']];
        $this->handler                = new ExceptionHandler($config);

        $secret  = bin2hex(random_bytes(12));
        $request = service('incomingrequest', null, false);
        $request->setHeader('Authorization', 'Bearer ' . $secret);
        $request->setHeader('API-Key', $secret);
        $request->setHeader('X-Internal-Key', $secret);
        $request->setHeader('X-Request-ID', 'example-request-id');
        Services::injectMock('request', $request);
        service('response')->setHeader('Set-Cookie', 'session=' . $secret);
        service('response')->setHeader('X-INTERNAL-KEY', $secret);
        service('superglobals')->setGet('filter', ['page' => 'example-page', 'internal' => $secret]);

        try {
            $output = $this->renderHtmlException(new RuntimeException('Diagnostics test.'));

            foreach (['Diagnostics', 'Request', 'Response', 'Server', 'Files (', 'Memory', '500 Internal Server Error', 'example-request-id', 'example-page'] as $label) {
                $this->assertStringContainsString($label, $output);
            }

            foreach (['Authorization', 'API-Key', 'Set-Cookie', 'X-Internal-Key', 'X-INTERNAL-KEY'] as $name) {
                $this->assertMatchesRegularExpression('#<dt>' . $name . '</dt>\\s*<dd><code>\\[redacted\\]</code></dd>#', $output);
            }

            $this->assertStringNotContainsString($secret, $output);
            $this->assertStringNotContainsString('Authorization', $this->extractCopyableErrorReport($output));
        } finally {
            service('superglobals')->unsetGet('filter');
            $this->resetServices();
        }
    }

    public function testDetailedHtmlShowsHeaderValuesAndMasksOtherFieldsByDefault(): void
    {
        $secret   = bin2hex(random_bytes(12));
        $request  = service('incomingrequest', null, false);
        $response = service('response', null, false);
        $request->setHeader('Content-Type', 'multipart/form-data; boundary=fixture');
        $request->setHeader('Content-Length', '128');
        $request->setHeader('Origin', 'https://example.test');
        $request->setHeader('X-Application-Mode', 'preview');
        $response->setHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->setHeader('Content-Length', '256');
        $response->removeHeader('Cache-Control');
        $response->setHeader('Cache-Control', 'no-store');
        $superglobals = service('superglobals');
        $superglobals->setGet('query-key', $secret)->setPost('form-key', $secret)->setCookie('custom-session', $secret);

        try {
            $output = $this->renderHtmlException(new RuntimeException('Diagnostics defaults test.'), $request, $response);

            $this->assertMatchesRegularExpression('#<dt>Content-Type</dt>\s*<dd><code>multipart/form-data; boundary=fixture</code></dd>#', $output);
            $this->assertMatchesRegularExpression('#<dt>Content-Type</dt>\s*<dd><code>text/plain; charset=UTF-8</code></dd>#', $output);
            $this->assertMatchesRegularExpression('#<dt>Content-Length</dt>\s*<dd><code>128</code></dd>#', $output);
            $this->assertMatchesRegularExpression('#<dt>Content-Length</dt>\s*<dd><code>256</code></dd>#', $output);
            $this->assertMatchesRegularExpression('#<dt>Origin</dt>\s*<dd><code>https://example\.test</code></dd>#', $output);
            $this->assertMatchesRegularExpression('#<dt>Cache-Control</dt>\s*<dd><code>no-store</code></dd>#', $output);
            $this->assertMatchesRegularExpression('#<dt>X-Application-Mode</dt>\s*<dd><code>preview</code></dd>#', $output);

            foreach (['query-key', 'form-key', 'custom-session'] as $name) {
                $this->assertMatchesRegularExpression('#<dt>' . $name . '</dt>\s*<dd><code>\[redacted\]</code></dd>#', $output);
            }

            $this->assertStringNotContainsString($secret, $output);
        } finally {
            $superglobals->unsetGet('query-key')->unsetPost('form-key')->unsetCookie('custom-session');
        }
    }

    public function testDetailedHtmlMasksExplicitlySensitiveCommonHeaders(): void
    {
        $config                       = new ExceptionsConfig();
        $config->sensitiveDataInTrace = ['content-type'];
        $this->handler                = new ExceptionHandler($config);

        $type    = sprintf('application/vnd.%s+json', bin2hex(random_bytes(8)));
        $request = service('incomingrequest', null, false);
        $request->setHeader('Content-Type', $type);

        $output = $this->renderHtmlException(new RuntimeException('Header validation test.'), $request);

        $this->assertMatchesRegularExpression('#<dt>Content-Type</dt>\s*<dd><code>\[redacted\]</code></dd>#', $output);
        $this->assertStringNotContainsString($type, $output);
    }

    public function testDetailedHtmlUsesTheRequestAndResponsePassedToTheHandler(): void
    {
        $config  = new App();
        $request = new IncomingRequest($config, new SiteURI($config, '/actual-request'), null, new UserAgent());
        $request->setHeader('X-Request-ID', 'actual-request-id');

        $response = service('response', null, false);
        $response->setHeader('X-Response-ID', 'actual-response-id');
        $response->setHeader('Content-Type', 'text/css; charset=UTF-8');

        $output = $this->renderHtmlException(new RuntimeException('Request and response test.'), $request, $response);

        $this->assertStringContainsString('actual-request-id', $output);
        $this->assertStringContainsString('actual-response-id', $output);
        $this->assertStringContainsString('<dd><code>text/css; charset=UTF-8</code></dd>', $output);
        $this->assertStringContainsString('<strong>HTTP 500</strong> Internal Server Error', $output);
        $this->assertStringContainsString('- Path: /actual-request', $this->extractCopyableErrorReport($output));
    }

    public function testServerDiagnosticsOnlyIncludeAllowlistedFields(): void
    {
        $superglobals = service('superglobals');
        $original     = $superglobals->getServerArray();
        $secret       = bin2hex(random_bytes(12));

        $superglobals->setServer('SERVER_SOFTWARE', 'Example Server');
        $superglobals->setServer('APPLICATION_SECRET', $secret);

        try {
            $output = $this->renderHtmlException(new RuntimeException('Server diagnostics test.'));

            $this->assertStringContainsString('<dt>SERVER_SOFTWARE</dt>', $output);
            $this->assertStringContainsString('Example Server', $output);
            $this->assertStringNotContainsString($secret, $output);
            $this->assertStringContainsString('<summary>Constants (', $output);
            $this->assertStringContainsString('<li><code>EXIT_SUCCESS</code></li>', $output);
            $this->assertStringContainsString('<li><code>COMPOSER_PATH</code></li>', $output);
        } finally {
            $superglobals->setServerArray($original);
        }
    }

    public function testConstantsShowNamesWithoutValues(): void
    {
        $suffix = bin2hex(random_bytes(5));
        $secret = bin2hex(random_bytes(12));
        $key    = sprintf('DB_PASS_%s', $suffix);
        $custom = sprintf('DEMO_CUSTOM_%s', $suffix);

        define($key, $secret);
        define($custom, ['value' => $secret]);

        $output = $this->renderHtmlException(new RuntimeException('Constants diagnostics test.'));

        $this->assertStringContainsString(sprintf('<li><code>%s</code></li>', $key), $output);
        $this->assertStringContainsString(sprintf('<li><code>%s</code></li>', $custom), $output);
        $this->assertStringNotContainsString($secret, $output);
    }

    public function testDetailedHtmlShowsUnlimitedMemoryLimit(): void
    {
        $this->backupIniValues(['memory_limit']);
        ini_set('memory_limit', '-1');

        try {
            $output = $this->renderHtmlException(new RuntimeException('Memory diagnostics test.'));

            $this->assertMatchesRegularExpression('#<dt>Memory limit</dt>\s*<dd><code>Unlimited</code></dd>#', $output);
        } finally {
            $this->restoreIniValues();
        }
    }

    public function testDetailedHtmlShowsUserAgentInRequestSummary(): void
    {
        service('superglobals')->setServer('HTTP_USER_AGENT', 'ExampleBrowser/1.0');

        try {
            $output = $this->renderHtmlException(new RuntimeException('Request summary test.'));

            $this->assertMatchesRegularExpression('#<dt>User agent</dt>\s*<dd><code>ExampleBrowser/1\.0</code></dd>#', $output);
            $this->assertStringContainsString('- User Agent: ExampleBrowser/1.0', $this->extractCopyableErrorReport($output));
        } finally {
            service('superglobals')->unsetServer('HTTP_USER_AGENT');
        }
    }

    public function testConfiguredUserAgentRedactionAlsoAppliesToCopyDetails(): void
    {
        $config                       = new ExceptionsConfig();
        $config->sensitiveDataInTrace = ['User-Agent'];
        $this->handler                = new ExceptionHandler($config);

        $agent = bin2hex(random_bytes(12));
        service('superglobals')->setServer('HTTP_USER_AGENT', $agent);

        try {
            $output = $this->renderHtmlException(new RuntimeException('Request summary test.'));

            $this->assertStringNotContainsString($agent, $output);
            $this->assertStringContainsString('- User Agent: [redacted]', $this->extractCopyableErrorReport($output));
        } finally {
            service('superglobals')->unsetServer('HTTP_USER_AGENT');
        }
    }

    public function testBacktraceCanRenderInternalCallsWithoutArguments(): void
    {
        $exception = array_map($this->createExceptionWithSensitiveTraceArgument(...), ['private value'])[0];
        $frames    = Frames::fromTrace($exception->getTrace());
        $rendered  = implode("\n", Backtrace::lines($frames, includeArguments: false));

        $this->assertNull($frames->take(1)[0]->file);
        $this->assertStringContainsString(self::class . '()->createExceptionWithSensitiveTraceArgument() [internal function]', $rendered);
        $this->assertStringNotContainsString('private value', $rendered);
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

    public function testHandleCLIExceptionWritesToStderr(): void
    {
        $request = Services::clirequest(null, false);
        /** @var Response $response */
        $response = service('response', null, false);
        $response->pretend();

        CITestStreamFilter::removeOutputFilter();

        $cause     = new RuntimeException('Root cause.');
        $exception = new RuntimeException('Something broke.', 0, $cause);
        $this->handler->handle($exception, $request, $response, 500, EXIT_ERROR);

        $this->assertStringContainsString('[' . RuntimeException::class . ']', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('Something broke.', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('Caused by:', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('Root cause.', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('Backtrace:', $this->getStreamFilterBuffer());
        $this->assertMatchesRegularExpression(
            sprintf('#Something broke\..*?> %d \|.*?Caused by:.*?Backtrace:#s', $exception->getLine()),
            $this->getStreamFilterBuffer(),
        );
        $this->assertSame(1, substr_count($this->getStreamFilterBuffer(), '  > '));
    }

    public function testCliAndLogsRedactConfiguredArgumentsAndDsns(): void
    {
        $this->backupIniValues(['zend.exception_ignore_args']);
        ini_set('zend.exception_ignore_args', '0');

        $config                       = new ExceptionsConfig();
        $config->sensitiveDataInTrace = ['credential'];
        $this->handler                = new ExceptionHandler($config);
        $secret                       = bin2hex(random_bytes(12));
        $dsn                          = sprintf('host=localhost password=%s', bin2hex(random_bytes(12)));
        $request                      = service('clirequest', null, false);
        Services::injectMock('request', $request);
        CITestStreamFilter::removeOutputFilter();

        try {
            $error = $this->exceptionWithCliArguments($secret, $dsn, 'trace-visible');
            (new Exceptions($config))->exceptionHandler($error);

            $this->assertLogContains('critical', "exceptionWithCliArguments([redacted], [redacted], 'trace-visible')");
            $this->assertFalse(TestLogger::didLog('critical', $secret, false));
            $this->assertFalse(TestLogger::didLog('critical', $dsn, false));

            $output = str_replace([' ', PHP_EOL], '', $this->getStreamFilterBuffer());
            $this->assertStringContainsString("exceptionWithCliArguments([redacted],[redacted],'trace-visible')", $output);
            $this->assertStringNotContainsString($secret, $output);
            $this->assertStringNotContainsString(str_replace(' ', '', $dsn), $output);
        } finally {
            $this->restoreIniValues();
        }
    }

    /**
     * The handler can only exit outside the testing environment, so it
     * runs in a separate process with the production environment.
     */
    #[DataProvider('provideHandleWebExceptionExitBehavior')]
    public function testHandleWebExceptionExitBehavior(
        string $accept,
        bool $workerMode,
        string $expectedOutput,
        int $expectedStatus,
    ): void {
        $escapedHomePath  = var_export(HOMEPATH, true);
        $escapedBootstrap = var_export(SYSTEMPATH . 'util_bootstrap.php', true);
        $escapedAccept    = var_export($accept, true);
        $escapedWorker    = var_export($workerMode, true);

        $code = <<<PHP
            chdir({$escapedHomePath});
            define('ENVIRONMENT', 'production');
            require {$escapedBootstrap};

            use CodeIgniter\\Boot;
            use CodeIgniter\\Debug\\ExceptionHandler;
            use CodeIgniter\\Exceptions\\PageNotFoundException;
            use Config\\Exceptions as ExceptionsConfig;

            (new \\ReflectionProperty(Boot::class, 'workerMode'))->setValue(null, {$escapedWorker});

            \$request = service('incomingrequest', null, false);
            \$request->setHeader('accept', {$escapedAccept});

            (new ExceptionHandler(new ExceptionsConfig()))->handle(
                PageNotFoundException::forPageNotFound(),
                \$request,
                service('response'),
                404,
                EXIT_UNKNOWN_FILE,
            );

            echo 'HANDLER RETURNED';
            PHP;

        $cmd = sprintf('%s -r %s 2>&1', escapeshellarg(PHP_BINARY), escapeshellarg($code));
        exec($cmd, $output, $status);
        $output = implode(PHP_EOL, $output);

        $this->assertSame($expectedStatus, $status, $output);
        $this->assertStringContainsString($expectedOutput, $output);

        if ($workerMode) {
            $this->assertStringEndsWith('HANDLER RETURNED', $output);
        } else {
            $this->assertStringNotContainsString('HANDLER RETURNED', $output);
        }
    }

    /**
     * @return iterable<string, array{string, bool, string, int}>
     */
    public static function provideHandleWebExceptionExitBehavior(): iterable
    {
        yield 'non-HTML, worker mode' => ['application/json', true, '""', 0];

        yield 'non-HTML, traditional' => ['application/json', false, '""', EXIT_UNKNOWN_FILE];

        yield 'HTML, worker mode' => ['text/html', true, '<title>404 - Page Not Found</title>', 0];

        yield 'HTML, traditional' => ['text/html', false, '<title>404 - Page Not Found</title>', EXIT_UNKNOWN_FILE];
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

        $this->assertSame(['password' => '[redacted]'], (array) $newTrace[0]['args'][0]);
        $this->assertSame(['password' => '[redacted]'], $newTrace[0]['args'][1]->default);
        $this->assertSame(['password' => '[redacted]'], $newTrace[0]['args'][2]);
        $this->assertSame(['password' => '[redacted]'], $newTrace[0]['args'][3]['default']);
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

    public function testMaskSensitiveDataLeavesFramesWithoutArgumentsIntact(): void
    {
        $maskSensitiveData = self::getPrivateMethodInvoker($this->handler, 'maskSensitiveData');
        $trace             = [['function' => 'run']];

        $this->assertSame($trace, $maskSensitiveData($trace, ['password']));
    }

    public function testHighlightFile(): void
    {
        $this->backupIniValues([
            'highlight.comment', 'highlight.default', 'highlight.html', 'highlight.keyword', 'highlight.string',
        ]);

        $highlightFile = self::getPrivateMethodInvoker($this->handler, 'highlightFile');
        $result        = $highlightFile(SUPPORTPATH . 'Controllers' . DIRECTORY_SEPARATOR . 'Hello.php', 16);

        $expected = file_get_contents(SUPPORTPATH . 'Debug' . DIRECTORY_SEPARATOR . 'highlightFile.html');

        $this->assertSame($expected, $result);

        $this->restoreIniValues();
    }

    private function exceptionWithApiArguments(#[SensitiveParameter] string $credential, string $public): RuntimeException
    {
        $exception             = new RuntimeException('API error response test.');
        [$sensitive, $visible] = $exception->getTrace()[0]['args'];

        $this->assertInstanceOf(SensitiveParameterValue::class, $sensitive);
        $this->assertSame($credential, $sensitive->getValue());
        $this->assertSame($public, $visible);

        return $exception;
    }

    private function exceptionWithCliArguments(string $credential, string $connection, string $label): RuntimeException
    {
        $exception = new RuntimeException('CLI error output test.');
        $this->assertSame([$credential, $connection, $label], $exception->getTrace()[0]['args']);

        return $exception;
    }

    private function exceptionWithTraceDepth(int $depth, ?RuntimeException $previous = null): RuntimeException
    {
        return $depth === 0
            ? new RuntimeException('Deep trace.', previous: $previous)
            : $this->exceptionWithTraceDepth($depth - 1, $previous);
    }

    private function createExceptionForArgumentPreview(string $sample): RuntimeException
    {
        $exception = $this->createExceptionWithPreviewInnerArgument('value passed to the inner call');
        $this->assertSame([$sample], $exception->getTrace()[1]['args']);

        return $exception;
    }

    private function createExceptionWithPreviewInnerArgument(string $inner): RuntimeException
    {
        $exception = new RuntimeException('Argument preview test.');
        $this->assertSame([$inner], $exception->getTrace()[0]['args']);

        return $exception;
    }

    private function forwardSensitiveTraceArgument(string $password): RuntimeException
    {
        return $this->createExceptionWithSensitiveTraceArgument($password);
    }

    private function createExceptionWithSensitiveTraceArgument(string $password): RuntimeException
    {
        return new RuntimeException('Trace argument test.');
    }

    private function extractCopyableErrorReport(string $output): string
    {
        $this->assertSame(1, preg_match('#<textarea[^>]*>\K.*?(?=</textarea>)#s', $output, $matches));

        return html_entity_decode($matches[0], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function renderHtmlException(RuntimeException $exception, ?IncomingRequest $request = null, ?ResponseInterface $response = null): string
    {
        $request ??= service('request');
        $response ??= service('response');
        $request->setHeader('Accept', 'text/html');

        ob_start();

        try {
            $this->handler->handle($exception, $request, $response, 500, EXIT_ERROR);

            return ob_get_contents();
        } finally {
            ob_end_clean();
        }
    }
}
