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

use CodeIgniter\Debug\Inspection\Frame;
use CodeIgniter\Debug\Inspection\Inspector;
use Config\Exceptions as ExceptionsConfig;
use ReflectionReference;
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

        $this->viewPath ??= rtrim($this->config->errorViewPath, '\\/ ') . DIRECTORY_SEPARATOR;
    }

    /**
     * Gathers the variables that will be made available to the view.
     *
     * @return array{
     *   title: string,
     *   type: string,
     *   code: int,
     *   message: string,
     *   file: string,
     *   line: int,
     *   trace: list<array<string, mixed>>,
     * }
     */
    protected function collectVars(Throwable $exception, int $statusCode): array
    {
        $trace = (new Inspector($exception))->rootCause()->getTrace();

        if ($this->config->sensitiveDataInTrace !== []) {
            $trace = $this->maskSensitiveData($trace, $this->config->sensitiveDataInTrace);
        }

        return $this->collectExceptionDetails($exception, $statusCode) + ['trace' => $trace];
    }

    /**
     * @return array{title: string, type: string, code: int, message: string, file: string, line: int}
     */
    protected function collectExceptionDetails(Throwable $exception, int $statusCode): array
    {
        $location = Frame::sourceLocation($exception->getFile(), $exception->getLine());

        return [
            'title'   => $exception::class,
            'type'    => $exception::class,
            'code'    => $statusCode,
            'message' => $exception->getMessage(),
            'file'    => $location['file'],
            'line'    => $location['line'] ?? $exception->getLine(),
        ];
    }

    /**
     * Mask sensitive data in the trace.
     *
     * @param list<array<string, mixed>> $trace
     * @param list<string>               $keysToMask
     *
     * @return list<array<string, mixed>>
     */
    protected function maskSensitiveData(array $trace, array $keysToMask, string $path = ''): array
    {
        foreach ($trace as $i => $line) {
            if (isset($line['args'])) {
                $trace[$i]['args'] = $this->maskData($line['args'], $keysToMask, $path);
            }
        }

        return $trace;
    }

    /**
     * Apply configured trace redaction rules to an array shown by an error view.
     *
     * @param array<array-key, mixed> $data
     * @param list<string>            $additionalKeys
     *
     * @return array<array-key, mixed>
     */
    protected function redactDiagnosticData(array $data, array $additionalKeys = [], bool $caseInsensitive = false): array
    {
        $keys = [...$this->config->sensitiveDataInTrace, ...$additionalKeys];

        if ($caseInsensitive) {
            $keys = array_map(strtolower(...), $keys);

            foreach ($data as $name => $value) {
                $data[$name] = in_array(strtolower((string) $name), $keys, true) ? Backtrace::REDACTED_TEXT : $value;
            }

            return $data;
        }

        return $this->maskData($data, $keys);
    }

    /**
     * @param array<array-key, mixed> $data
     * @param list<string>            $sensitiveKeys
     *
     * @return array<array-key, mixed>
     */
    protected function diagnosticFields(array $data, string $section, array $sensitiveKeys = []): array
    {
        $allowed = $this->config->diagnosticValues[$section] ?? [];

        foreach (array_keys($data) as $name) {
            if (! in_array((string) $name, $allowed, true)) {
                $data[$name] = Backtrace::REDACTED_TEXT;
            }
        }

        return $this->redactDiagnosticData($data, $sensitiveKeys);
    }

    /**
     * @return array<array-key, string>
     */
    protected function previewDiagnosticArguments(Frame $frame): array
    {
        return Backtrace::argumentPreview($frame, $this->config->sensitiveDataInTrace);
    }

    /**
     * @param array<array-key, mixed>|object $args
     * @param list<string>                   $keysToMask
     * @param array<int|string, true>        $ancestors
     *
     * @return array<array-key, mixed>|object
     */
    private function maskData(mixed $args, array $keysToMask, string $path = '', array $ancestors = []): mixed
    {
        if (is_object($args)) {
            $id = spl_object_id($args);

            if (isset($ancestors[$id])) {
                return $args;
            }

            $ancestors[$id] = true;
        }

        foreach ($keysToMask as $key) {
            $explode = explode('/', $key);
            $index   = end($explode);

            if (str_starts_with(strrev($path . '/' . $index), strrev($key))) {
                if (is_array($args) && array_key_exists($index, $args)) {
                    $args[$index] = Backtrace::REDACTED_TEXT;
                } elseif (
                    is_object($args) && property_exists($args, $index)
                    && isset($args->{$index}) && is_scalar($args->{$index})
                ) {
                    $args->{$index} = Backtrace::REDACTED_TEXT;
                }
            }
        }

        if (is_array($args)) {
            foreach ($args as $pathKey => $subarray) {
                $reference = ReflectionReference::fromArrayElement($args, $pathKey)?->getId();

                if ($reference !== null && isset($ancestors[$reference])) {
                    continue;
                }

                $branch = $reference === null ? $ancestors : $ancestors + [$reference => true];

                $args[$pathKey] = $this->maskData($subarray, $keysToMask, $path . '/' . $pathKey, $branch);
            }
        } elseif (is_object($args)) {
            foreach ($args as $pathKey => $subarray) {
                $args->{$pathKey} = $this->maskData($subarray, $keysToMask, $path . '/' . $pathKey, $ancestors);
            }
        }

        return $args;
    }

    /**
     * Describes memory usage in real-world units. Intended for use
     * with memory_get_usage, etc.
     */
    protected static function describeMemory(int $bytes): string
    {
        helper('number');

        return number_to_size($bytes, 2);
    }

    /**
     * Creates a syntax-highlighted version of a PHP file.
     *
     * @return bool|string
     */
    protected static function highlightFile(string $file, int $lineNumber, int $lines = 15)
    {
        if ($file === '' || ! is_readable($file)) {
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
        } catch (Throwable) {
            return false;
        }

        $source = str_replace(["\r\n", "\r"], "\n", $source);
        $source = explode("\n", highlight_string($source, true));

        // We have to remove these tags since we're preparing the result
        // ourselves and these tags are added manually at the end.
        $source = str_replace(['<pre><code>', '</code></pre>'], '', $source);

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
        $spans = 0;

        foreach ($source as $n => $row) {
            $spans += substr_count($row, '<span') - substr_count($row, '</span');
            $row = str_replace(["\r", "\n"], ['', ''], $row);

            if (($n + $start + 1) === $lineNumber) {
                preg_match_all('#<[^>]+>#', $row, $tags);

                $out .= sprintf(
                    "<span class='line highlight'><span class='number'>{$format}</span> %s\n</span>%s",
                    $n + $start + 1,
                    strip_tags($row),
                    implode('', $tags[0]),
                );
            } else {
                $out .= sprintf('<span class="line"><span class="number">' . $format . '</span> %s', $n + $start + 1, $row) . "\n";
                // We're closing only one span tag we added manually line before,
                // so we have to increment $spans count to close this tag later.
                $spans++;
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
        $this->renderWithViewData($exception, $statusCode, $viewFile, []);
    }

    /**
     * Render an error view with additional variables supplied by the handler.
     *
     * @param array<string, mixed> $viewData
     */
    protected function renderWithViewData(Throwable $exception, int $statusCode, ?string $viewFile, array $viewData): void
    {
        if ($viewFile === null) {
            echo 'The error view file was not specified. Cannot display error view.';

            exit(1);
        }

        if (! is_file($viewFile)) {
            echo 'The error view file "' . $viewFile . '" was not found. Cannot display error view.';

            exit(1);
        }

        echo (function () use ($exception, $statusCode, $viewFile, $viewData): string {
            $vars = $this->collectVars($exception, $statusCode) + ['config' => $this->config] + $viewData;
            extract($vars, EXTR_SKIP);

            // CLI error views output to STDERR/STDOUT, so ob_start() does not work.
            ob_start();
            include $viewFile;

            return ob_get_clean();
        })();
    }
}
