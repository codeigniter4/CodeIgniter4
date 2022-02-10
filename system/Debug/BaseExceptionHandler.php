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
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use Throwable;

/**
 * Provides common functions for exception handlers,
 * especially around displaying the output.
 */
abstract class BaseExceptionHandler
{
    use ResponseTrait;

    /**
     * Nesting level of the output buffering mechanism
     */
    protected int $obLevel;

    protected int $statusCode;
    protected Throwable $exception;
    protected string $view;
    protected RequestInterface $request;
    protected Response $response;
    protected string $viewPath;
    protected int $exitCode;

    public function __construct()
    {
        $this->obLevel  = ob_get_level();
        $this->viewPath = rtrim(config('Exceptions')->errorViewPath, '\\/ ') . DIRECTORY_SEPARATOR;
    }

    /**
     * The main entry point into the handler.
     *
     * @return mixed
     */
    abstract public function handle();

    /**
     * Set the HTTP status code that is being thrown.
     *
     * @return $this
     */
    public function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Set the exception that was originally thrown.
     *
     * @return $this
     */
    public function setException(Throwable $exception)
    {
        $this->exception = $exception;

        return $this;
    }

    /**
     * Set the request used.
     *
     * @return $this
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Sets the response that will be used
     *
     * @return $this
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Sets the exit code that should be used when exiting the script.
     *
     * @return $this
     */
    public function setExitCode(int $code)
    {
        $this->exitCode = $code;

        return $this;
    }

    /**
     * Gathers the variables that will be made available to the view.
     */
    protected function collectVars(Throwable $exception, int $statusCode): array
    {
        $trace = $exception->getTrace();

        if (config('Exceptions')->sensitiveDataInTrace !== []) {
            $this->maskSensitiveData($trace, config('Exceptions')->sensitiveDataInTrace);
        }

        return [
            'title'   => get_class($exception),
            'type'    => get_class($exception),
            'code'    => $statusCode,
            'message' => $exception->getMessage() ?? '(null)',
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'trace'   => $trace,
        ];
    }

    /**
     * Mask sensitive data in the trace.
     *
     * @param array|object $trace
     */
    protected function maskSensitiveData(&$trace, array $keysToMask, string $path = '')
    {
        foreach ($keysToMask as $keyToMask) {
            $explode = explode('/', $keyToMask);
            $index   = end($explode);

            if (strpos(strrev($path . '/' . $index), strrev($keyToMask)) === 0) {
                if (is_array($trace) && array_key_exists($index, $trace)) {
                    $trace[$index] = '******************';
                } elseif (is_object($trace) && property_exists($trace, $index) && isset($trace->{$index})) {
                    $trace->{$index} = '******************';
                }
            }
        }

        if (is_object($trace)) {
            $trace = get_object_vars($trace);
        }

        if (is_array($trace)) {
            foreach ($trace as $pathKey => $subarray) {
                $this->maskSensitiveData($subarray, $keysToMask, $path . '/' . $pathKey);
            }
        }
    }

    /**
     * Describes memory usage in real-world units. Intended for use
     * with memory_get_usage, etc.
     */
    protected static function describeMemory(int $bytes): string
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
     */
    protected static function highlightFile(string $file, int $lineNumber, int $lines = 15)
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

    /**
     * Given an exception and status code will display the error to the client.
     *
     * @param mixed|null $viewFile
     */
    protected function render($viewFile = null)
    {
        if (empty($viewFile) || ! is_file($viewFile)) {
            echo 'The error view files were not found. Cannot render exception trace.';

            exit(1);
        }

        if (ob_get_level() > $this->obLevel + 1) {
            ob_end_clean();
        }

        $exception  = $this->exception;
        $statusCode = $this->statusCode;

        echo(function () use ($exception, $statusCode, $viewFile): string {
            $vars = $this->collectVars($exception, $statusCode);
            extract($vars, EXTR_SKIP);

            ob_start();
            include $viewFile;

            return ob_get_clean();
        })();
    }

    /**
     * This makes nicer looking paths for the error output.
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
}
