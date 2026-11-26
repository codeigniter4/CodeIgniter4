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

use CodeIgniter\CLI\Output;
use CodeIgniter\Debug\Inspection\CodeSnippet;
use CodeIgniter\Debug\Inspection\Frame;
use CodeIgniter\Debug\Inspection\Frames;
use CodeIgniter\Debug\Inspection\Inspector;
use Throwable;

/**
 * Writes exception details to the terminal.
 *
 * @internal
 */
final class ConsoleExceptionRenderer
{
    /**
     * Snippet prefix width excluding the line number digits.
     */
    private const SNIPPET_PREFIX_WIDTH = 7;

    /**
     * Maximum indent for calls below their numbered locations.
     */
    private const BACKTRACE_CALL_INDENT = 7;

    /**
     * Render the exception, source excerpt, causes, and enabled backtrace.
     *
     * @param list<string> $sensitiveKeys
     */
    public static function render(Throwable $exception, string $message, Output $output, array $sensitiveKeys = []): void
    {
        $showBacktrace = (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE) || $output->isVerbose();

        self::writeSummary($exception, $message, $output);
        self::writeSnippet($exception->getFile(), $exception->getLine(), $output);
        $output->deferNewLine();

        $chain = (new Inspector($exception))->chain();
        self::writeCauses(array_slice($chain, 1), $output);

        if ($showBacktrace) {
            self::writeBacktrace($chain[array_key_last($chain)], $output, $sensitiveKeys);
        }
    }

    private static function writeSummary(Throwable $exception, string $message, Output $output): void
    {
        $output->write(sprintf('[%s]', $exception::class), 'light_gray', 'red');
        $output->write($message);

        $location = Backtrace::location($exception->getFile(), $exception->getLine(), cli: true);
        $output->write(sprintf('in %s', $output->color($location, 'green')));
    }

    /**
     * Write a terminal-width source excerpt at the exception's throw site.
     */
    private static function writeSnippet(string $file, int $line, Output $output): void
    {
        $location = Frame::sourceLocation($file, $line);

        $line  = $location['line'] ?? $line;
        $lines = CodeSnippet::get($location['file'], $line, highlight: false);

        if ($lines === []) {
            return;
        }

        $digits = strlen((string) array_key_last($lines));
        $width  = $output->getTerminal()->getWidth() - $digits - self::SNIPPET_PREFIX_WIDTH;

        if ($width < 1) {
            return;
        }

        $output->deferNewLine();

        foreach ($lines as $number => $text) {
            $current = $number === $line;
            $source  = addcslashes(str_replace("\t", '    ', $text), "\0..\37\177");
            $output->write(sprintf(
                '  %s %*d | %s',
                $current ? '>' : ' ',
                $digits,
                $number,
                mb_strimwidth($source, 0, $width, '…', 'UTF-8'),
            ), $current ? 'yellow' : '');
        }
    }

    /**
     * @param list<Throwable> $causes
     */
    private static function writeCauses(array $causes, Output $output): void
    {
        foreach ($causes as $cause) {
            $output->write('  Caused by:');
            $output->write(sprintf('  %s', $output->color(sprintf('[%s]', $cause::class), 'light_gray', 'red')));
            $output->write(sprintf('  %s', $cause->getMessage()));
            $location = Backtrace::location($cause->getFile(), $cause->getLine(), cli: true);
            $output->write(sprintf('  in %s', $output->color($location, 'green')));
            $output->deferNewLine();
        }
    }

    /**
     * Write the numbered trace with calls wrapped to the terminal width.
     *
     * @param list<string> $sensitiveKeys
     */
    private static function writeBacktrace(Throwable $rootCause, Output $output, array $sensitiveKeys): void
    {
        $trace = Frames::fromException($rootCause);

        $output->write('Backtrace:', 'green');

        $columns = max(1, $output->getTerminal()->getWidth());
        $padding = min(self::BACKTRACE_CALL_INDENT, max(0, $columns - 2));
        $indent  = str_repeat(' ', $padding);
        $width   = $columns - $padding;

        foreach ($trace as $index => $frame) {
            $location = Backtrace::location($frame->file, $frame->line, cli: true);
            $output->write(sprintf('%3d    %s', $index + 1, $output->color($location, 'yellow')));

            $call = Backtrace::call($frame, $sensitiveKeys);

            foreach (explode(PHP_EOL, $output->wrap($call, $width)) as $line) {
                // The standard wrapper leaves words wider than a line intact.
                while (mb_strwidth($line, 'UTF-8') > $width) {
                    $part = mb_strimwidth($line, 0, $width, '', 'UTF-8');
                    $part = $part === '' ? mb_substr($line, 0, 1, 'UTF-8') : $part;
                    $output->write($indent . $part);
                    $line = substr($line, strlen($part));
                }

                $output->write($indent . $line);
            }

            $output->deferNewLine();
        }
    }
}
