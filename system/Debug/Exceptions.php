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

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Exceptions\HasExitCodeInterface;
use CodeIgniter\Exceptions\HTTPExceptionInterface;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Exceptions as ExceptionsConfig;
use Config\Paths;
use Config\Services;
use ErrorException;
use Psr\Log\LogLevel;
use Throwable;

/**
 * Exceptions manager
 *
 * @see \CodeIgniter\Debug\ExceptionsTest
 */
class Exceptions
{
    use ResponseTrait;

    /**
     * Nesting level of the output buffering mechanism
     *
     * @var int
     *
     * @deprecated 4.4.0 No longer used. Moved to BaseExceptionHandler.
     */
    public $ob_level;

    /**
     * The path to the directory containing the
     * cli and html error view directories.
     *
     * @var string
     *
     * @deprecated 4.4.0 No longer used. Moved to BaseExceptionHandler.
     */
    protected $viewPath;

    /**
     * Config for debug exceptions.
     *
     * @var ExceptionsConfig
     */
    protected $config;

    /**
     * The request.
     *
     * @var RequestInterface|null
     */
    protected $request;

    /**
     * The outgoing response.
     *
     * @var ResponseInterface
     */
    protected $response;

    private ?Throwable $exceptionCaughtByExceptionHandler = null;

    public function __construct(ExceptionsConfig $config)
    {
        // For backward compatibility
        $this->ob_level = ob_get_level();
        $this->viewPath = rtrim($config->errorViewPath, '\\/ ') . DIRECTORY_SEPARATOR;

        $this->config = $config;

        // workaround for upgraded users
        // This causes "Deprecated: Creation of dynamic property" in PHP 8.2.
        // @TODO remove this after dropping PHP 8.1 support.
        if (! isset($this->config->sensitiveDataInTrace)) {
            $this->config->sensitiveDataInTrace = [];
        }
        if (! isset($this->config->logDeprecations, $this->config->deprecationLogLevel)) {
            $this->config->logDeprecations     = false;
            $this->config->deprecationLogLevel = LogLevel::WARNING;
        }
    }

    /**
     * Responsible for registering the error, exception and shutdown
     * handling of our application.
     *
     * @codeCoverageIgnore
     *
     * @return void
     */
    public function initialize()
    {
        set_exception_handler([$this, 'exceptionHandler']);
        set_error_handler([$this, 'errorHandler']);
        register_shutdown_function([$this, 'shutdownHandler']);
    }

    /**
     * Catches any uncaught errors and exceptions, including most Fatal errors
     * (Yay PHP7!). Will log the error, display it if display_errors is on,
     * and fire an event that allows custom actions to be taken at this point.
     *
     * @return void
     */
    public function exceptionHandler(Throwable $exception)
    {
        $this->exceptionCaughtByExceptionHandler = $exception;

        [$statusCode, $exitCode] = $this->determineCodes($exception);

        if ($this->config->log === true && ! in_array($statusCode, $this->config->ignoreCodes, true)) {
            log_message('critical', get_class($exception) . ": {message}\nin {exFile} on line {exLine}.\n{trace}", [
                'message' => $exception->getMessage(),
                'exFile'  => clean_path($exception->getFile()), // {file} refers to THIS file
                'exLine'  => $exception->getLine(), // {line} refers to THIS line
                'trace'   => self::renderBacktrace($exception->getTrace()),
            ]);

            // Get the first exception.
            $last = $exception;

            while ($prevException = $last->getPrevious()) {
                $last = $prevException;

                log_message('critical', '[Caused by] ' . get_class($prevException) . ": {message}\nin {exFile} on line {exLine}.\n{trace}", [
                    'message' => $prevException->getMessage(),
                    'exFile'  => clean_path($prevException->getFile()), // {file} refers to THIS file
                    'exLine'  => $prevException->getLine(), // {line} refers to THIS line
                    'trace'   => self::renderBacktrace($prevException->getTrace()),
                ]);
            }
        }

        $this->request  = Services::request();
        $this->response = Services::response();

        if (method_exists($this->config, 'handler')) {
            // Use new ExceptionHandler
            $handler = $this->config->handler($statusCode, $exception);
            $handler->handle(
                $exception,
                $this->request,
                $this->response,
                $statusCode,
                $exitCode
            );

            return;
        }

        // For backward compatibility
        if (! is_cli()) {
            try {
                $this->response->setStatusCode($statusCode);
            } catch (HTTPException $e) {
                // Workaround for invalid HTTP status code.
                $statusCode = 500;
                $this->response->setStatusCode($statusCode);
            }

            if (! headers_sent()) {
                header(sprintf('HTTP/%s %s %s', $this->request->getProtocolVersion(), $this->response->getStatusCode(), $this->response->getReasonPhrase()), true, $statusCode);
            }

            if (strpos($this->request->getHeaderLine('accept'), 'text/html') === false) {
                $this->respond(ENVIRONMENT === 'development' ? $this->collectVars($exception, $statusCode) : '', $statusCode)->send();

                exit($exitCode);
            }
        }

        $this->render($exception, $statusCode);

        exit($exitCode);
    }

    /**
     * The callback to be registered to `set_error_handler()`.
     *
     * @return bool
     *
     * @throws ErrorException
     *
     * @codeCoverageIgnore
     */
    public function errorHandler(int $severity, string $message, ?string $file = null, ?int $line = null)
    {
        if ($this->isDeprecationError($severity)) {
            if (! $this->config->logDeprecations || (bool) env('CODEIGNITER_SCREAM_DEPRECATIONS')) {
                throw new ErrorException($message, 0, $severity, $file, $line);
            }

            return $this->handleDeprecationError($message, $file, $line);
        }

        if ((error_reporting() & $severity) !== 0) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        }

        return false; // return false to propagate the error to PHP standard error handler
    }

    /**
     * Checks to see if any errors have happened during shutdown that
     * need to be caught and handle them.
     *
     * @codeCoverageIgnore
     *
     * @return void
     */
    public function shutdownHandler()
    {
        $error = error_get_last();

        if ($error === null) {
            return;
        }

        ['type' => $type, 'message' => $message, 'file' => $file, 'line' => $line] = $error;

        if ($this->exceptionCaughtByExceptionHandler instanceof Throwable) {
            $message .= "\n【Previous Exception】\n"
                . get_class($this->exceptionCaughtByExceptionHandler) . "\n"
                . $this->exceptionCaughtByExceptionHandler->getMessage() . "\n"
                . $this->exceptionCaughtByExceptionHandler->getTraceAsString();
        }

        if (in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE], true)) {
            $this->exceptionHandler(new ErrorException($message, 0, $type, $file, $line));
        }
    }

    /**
     * Determines the view to display based on the exception thrown,
     * whether an HTTP or CLI request, etc.
     *
     * @return string The path and filename of the view file to use
     *
     * @deprecated 4.4.0 No longer used. Moved to ExceptionHandler.
     */
    protected function determineView(Throwable $exception, string $templatePath): string
    {
        // Production environments should have a custom exception file.
        $view         = 'production.php';
        $templatePath = rtrim($templatePath, '\\/ ') . DIRECTORY_SEPARATOR;

        if (
            in_array(
                strtolower(ini_get('display_errors')),
                ['1', 'true', 'on', 'yes'],
                true
            )
        ) {
            $view = 'error_exception.php';
        }

        // 404 Errors
        if ($exception instanceof PageNotFoundException) {
            return 'error_404.php';
        }

        // Allow for custom views based upon the status code
        if (is_file($templatePath . 'error_' . $exception->getCode() . '.php')) {
            return 'error_' . $exception->getCode() . '.php';
        }

        return $view;
    }

    /**
     * Given an exception and status code will display the error to the client.
     *
     * @return void
     *
     * @deprecated 4.4.0 No longer used. Moved to BaseExceptionHandler.
     */
    protected function render(Throwable $exception, int $statusCode)
    {
        // Determine possible directories of error views
        $path    = $this->viewPath;
        $altPath = rtrim((new Paths())->viewDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR;

        $path    .= (is_cli() ? 'cli' : 'html') . DIRECTORY_SEPARATOR;
        $altPath .= (is_cli() ? 'cli' : 'html') . DIRECTORY_SEPARATOR;

        // Determine the views
        $view    = $this->determineView($exception, $path);
        $altView = $this->determineView($exception, $altPath);

        // Check if the view exists
        if (is_file($path . $view)) {
            $viewFile = $path . $view;
        } elseif (is_file($altPath . $altView)) {
            $viewFile = $altPath . $altView;
        }

        if (! isset($viewFile)) {
            echo 'The error view files were not found. Cannot render exception trace.';

            exit(1);
        }

        echo (function () use ($exception, $statusCode, $viewFile): string {
            $vars = $this->collectVars($exception, $statusCode);
            extract($vars, EXTR_SKIP);

            ob_start();
            include $viewFile;

            return ob_get_clean();
        })();
    }

    /**
     * Gathers the variables that will be made available to the view.
     *
     * @deprecated 4.4.0 No longer used. Moved to BaseExceptionHandler.
     */
    protected function collectVars(Throwable $exception, int $statusCode): array
    {
        // Get the first exception.
        $firstException = $exception;

        while ($prevException = $firstException->getPrevious()) {
            $firstException = $prevException;
        }

        $trace = $firstException->getTrace();

        if ($this->config->sensitiveDataInTrace !== []) {
            $trace = $this->maskSensitiveData($trace, $this->config->sensitiveDataInTrace);
        }

        return [
            'title'   => get_class($exception),
            'type'    => get_class($exception),
            'code'    => $statusCode,
            'message' => $exception->getMessage(),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'trace'   => $trace,
        ];
    }

    /**
     * Mask sensitive data in the trace.
     *
     * @param array $trace
     *
     * @return array
     *
     * @deprecated 4.4.0 No longer used. Moved to BaseExceptionHandler.
     */
    protected function maskSensitiveData($trace, array $keysToMask, string $path = '')
    {
        foreach ($trace as $i => $line) {
            $trace[$i]['args'] = $this->maskData($line['args'], $keysToMask);
        }

        return $trace;
    }

    /**
     * @param array|object $args
     *
     * @return array|object
     *
     * @deprecated 4.4.0 No longer used. Moved to BaseExceptionHandler.
     */
    private function maskData($args, array $keysToMask, string $path = '')
    {
        foreach ($keysToMask as $keyToMask) {
            $explode = explode('/', $keyToMask);
            $index   = end($explode);

            if (strpos(strrev($path . '/' . $index), strrev($keyToMask)) === 0) {
                if (is_array($args) && array_key_exists($index, $args)) {
                    $args[$index] = '******************';
                } elseif (
                    is_object($args) && property_exists($args, $index)
                    && isset($args->{$index}) && is_scalar($args->{$index})
                ) {
                    $args->{$index} = '******************';
                }
            }
        }

        if (is_array($args)) {
            foreach ($args as $pathKey => $subarray) {
                $args[$pathKey] = $this->maskData($subarray, $keysToMask, $path . '/' . $pathKey);
            }
        } elseif (is_object($args)) {
            foreach ($args as $pathKey => $subarray) {
                $args->{$pathKey} = $this->maskData($subarray, $keysToMask, $path . '/' . $pathKey);
            }
        }

        return $args;
    }

    /**
     * Determines the HTTP status code and the exit status code for this request.
     */
    protected function determineCodes(Throwable $exception): array
    {
        $statusCode = 500;
        $exitStatus = EXIT_ERROR;

        if ($exception instanceof HTTPExceptionInterface) {
            $statusCode = $exception->getCode();
        }

        if ($exception instanceof HasExitCodeInterface) {
            $exitStatus = $exception->getExitCode();
        }

        return [$statusCode, $exitStatus];
    }

    private function isDeprecationError(int $error): bool
    {
        $deprecations = E_DEPRECATED | E_USER_DEPRECATED;

        return ($error & $deprecations) !== 0;
    }

    /**
     * @return true
     */
    private function handleDeprecationError(string $message, ?string $file = null, ?int $line = null): bool
    {
        // Remove the trace of the error handler.
        $trace = array_slice(debug_backtrace(), 2);

        log_message(
            $this->config->deprecationLogLevel,
            "[DEPRECATED] {message} in {errFile} on line {errLine}.\n{trace}",
            [
                'message' => $message,
                'errFile' => clean_path($file ?? ''),
                'errLine' => $line ?? 0,
                'trace'   => self::renderBacktrace($trace),
            ]
        );

        return true;
    }

    // --------------------------------------------------------------------
    // Display Methods
    // --------------------------------------------------------------------

    /**
     * This makes nicer looking paths for the error output.
     *
     * @deprecated Use dedicated `clean_path()` function.
     */
    public static function cleanPath(string $file): string
    {
        switch (true) {
            case strpos($file, APPPATH) === 0:
                $file = 'APPPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(APPPATH));
                break;

            case strpos($file, SYSTEMPATH) === 0:
                $file = 'SYSTEMPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(SYSTEMPATH));
                break;

            case strpos($file, FCPATH) === 0:
                $file = 'FCPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(FCPATH));
                break;

            case defined('VENDORPATH') && strpos($file, VENDORPATH) === 0:
                $file = 'VENDORPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(VENDORPATH));
                break;
        }

        return $file;
    }

    /**
     * Describes memory usage in real-world units. Intended for use
     * with memory_get_usage, etc.
     *
     * @deprecated 4.4.0 No longer used. Moved to BaseExceptionHandler.
     */
    public static function describeMemory(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . 'B';
        }

        if ($bytes < 1_048_576) {
            return round($bytes / 1024, 2) . 'KB';
        }

        return round($bytes / 1_048_576, 2) . 'MB';
    }

    /**
     * Creates a syntax-highlighted version of a PHP file.
     *
     * @return bool|string
     *
     * @deprecated 4.4.0 No longer used. Moved to BaseExceptionHandler.
     */
    public static function highlightFile(string $file, int $lineNumber, int $lines = 15)
    {
        if (empty($file) || ! is_readable($file)) {
            return false;
        }

        // Set our highlight colors:
        if (function_exists('ini_set')) {
            ini_set('highlight.comment', '#767a7e; font-style: italic');
            ini_set('highlight.default', '#c7c7c7');
            ini_set('highlight.html', '#06B');
            ini_set('highlight.keyword', '#f1ce61;');
            ini_set('highlight.string', '#869d6a');
        }

        try {
            $source = file_get_contents($file);
        } catch (Throwable $e) {
            return false;
        }

        $source = str_replace(["\r\n", "\r"], "\n", $source);
        $source = explode("\n", highlight_string($source, true));
        $source = str_replace('<br />', "\n", $source[1]);
        $source = explode("\n", str_replace("\r\n", "\n", $source));

        // Get just the part to show
        $start = max($lineNumber - (int) round($lines / 2), 0);

        // Get just the lines we need to display, while keeping line numbers...
        $source = array_splice($source, $start, $lines, true);

        // Used to format the line number in the source
        $format = '% ' . strlen((string) ($start + $lines)) . 'd';

        $out = '';
        // Because the highlighting may have an uneven number
        // of open and close span tags on one line, we need
        // to ensure we can close them all to get the lines
        // showing correctly.
        $spans = 1;

        foreach ($source as $n => $row) {
            $spans += substr_count($row, '<span') - substr_count($row, '</span');
            $row = str_replace(["\r", "\n"], ['', ''], $row);

            if (($n + $start + 1) === $lineNumber) {
                preg_match_all('#<[^>]+>#', $row, $tags);

                $out .= sprintf(
                    "<span class='line highlight'><span class='number'>{$format}</span> %s\n</span>%s",
                    $n + $start + 1,
                    strip_tags($row),
                    implode('', $tags[0])
                );
            } else {
                $out .= sprintf('<span class="line"><span class="number">' . $format . '</span> %s', $n + $start + 1, $row) . "\n";
            }
        }

        if ($spans > 0) {
            $out .= str_repeat('</span>', $spans);
        }

        return '<pre><code>' . $out . '</code></pre>';
    }

    private static function renderBacktrace(array $backtrace): string
    {
        $backtraces = [];

        foreach ($backtrace as $index => $trace) {
            $frame = $trace + ['file' => '[internal function]', 'line' => '', 'class' => '', 'type' => '', 'args' => []];

            if ($frame['file'] !== '[internal function]') {
                $frame['file'] = sprintf('%s(%s)', $frame['file'], $frame['line']);
            }

            unset($frame['line']);
            $idx = $index;
            $idx = str_pad((string) ++$idx, 2, ' ', STR_PAD_LEFT);

            $args = implode(', ', array_map(static function ($value): string {
                switch (true) {
                    case is_object($value):
                        return sprintf('Object(%s)', get_class($value));

                    case is_array($value):
                        return $value !== [] ? '[...]' : '[]';

                    case $value === null:
                        return 'null';

                    case is_resource($value):
                        return sprintf('resource (%s)', get_resource_type($value));

                    default:
                        return var_export($value, true);
                }
            }, $frame['args']));

            $backtraces[] = sprintf(
                '%s %s: %s%s%s(%s)',
                $idx,
                clean_path($frame['file']),
                $frame['class'],
                $frame['type'],
                $frame['function'],
                $args
            );
        }

        return implode("\n", $backtraces);
    }
}
