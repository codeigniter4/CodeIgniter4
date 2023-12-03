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

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Exceptions as ExceptionsConfig;
use Throwable;

/**
 * Provides common functions for exception handlers,
 * especially around displaying the output.
 */
abstract class BaseExceptionHandler
{
    /**
     * Config for debug exceptions.
     */
    protected ExceptionsConfig $config;

    /**
     * Nesting level of the output buffering mechanism
     */
    protected int $obLevel;

    /**
     * The path to the directory containing the
     * cli and html error view directories.
     */
    protected ?string $viewPath = null;

    public function __construct(ExceptionsConfig $config)
    {
        $this->config = $config;

        $this->obLevel = ob_get_level();

        if ($this->viewPath === null) {
            $this->viewPath = rtrim($this->config->errorViewPath, '\\/ ') . DIRECTORY_SEPARATOR;
        }
    }

    /**
     * The main entry point into the handler.
     *
     * @return void
     */
    abstract public function handle(
        Throwable $exception,
        RequestInterface $request,
        ResponseInterface $response,
        int $statusCode,
        int $exitCode
    );

    /**
     * Gathers the variables that will be made available to the view.
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
     */
    protected function maskSensitiveData(array $trace, array $keysToMask, string $path = ''): array
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
     * Describes memory usage in real-world units. Intended for use
     * with memory_get_usage, etc.
     *
     * @used-by app/Views/errors/html/error_exception.php
     */
    protected static function describeMemory(int $bytes): string
    {
        helper('number');

        return number_to_size($bytes, 2);
    }

    /**
     * Creates a syntax-highlighted version of a PHP file.
     *
     * @used-by app/Views/errors/html/error_exception.php
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
     * @param string|null $viewFile
     */
    protected function render(Throwable $exception, int $statusCode, $viewFile = null): void
    {
        if (empty($viewFile) || ! is_file($viewFile)) {
            echo 'The error view files were not found. Cannot render exception trace.';

            exit(1);
        }

        echo (function () use ($exception, $statusCode, $viewFile): string {
            $vars = $this->collectVars($exception, $statusCode);
            extract($vars, EXTR_SKIP);

            // CLI error views output to STDERR/STDOUT, so ob_start() does not work.
            ob_start();
            include $viewFile;

            return ob_get_clean();
        })();
    }
}
