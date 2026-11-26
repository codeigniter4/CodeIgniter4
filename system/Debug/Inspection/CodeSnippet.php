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

namespace CodeIgniter\Debug\Inspection;

use SplFileObject;
use Throwable;

/**
 * Reads bounded PHP source excerpts.
 *
 * @internal
 */
final class CodeSnippet
{
    /**
     * Lines of context on each side of the selected line in HTML.
     */
    private const CONTEXT_LINES = 7;

    /**
     * Lines of context on each side of the selected line in plain text.
     */
    private const PLAIN_CONTEXT_LINES = 3;

    /**
     * Largest source file eligible for a snippet, in bytes.
     */
    private const MAX_FILE_SIZE = 1_048_576;

    /**
     * Source bytes shown per line before adding an ellipsis.
     */
    private const MAX_LINE_BYTES = 1_000;

    /**
     * Read a source excerpt around the requested line as escaped HTML or plain text, keyed by one-based line number.
     *
     * @return array<int, string>
     */
    public static function get(?string $file, ?int $line, bool $highlight = true): array
    {
        if ($file === null || $line === null || $line < 1) {
            return [];
        }

        try {
            // Resolve a local file before opening it and only show PHP source.
            $path = realpath($file);

            if ($path === false || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'php' || ! is_file($path)) {
                return [];
            }

            $source = new SplFileObject($path, 'rb');

            if ($source->getSize() > self::MAX_FILE_SIZE) {
                return [];
            }

            // Read one extra byte to detect growth after the size check.
            $contents = $source->fread(self::MAX_FILE_SIZE + 1);

            if ($contents === false || strlen($contents) > self::MAX_FILE_SIZE) {
                return [];
            }

            return $highlight ? self::highlight($contents, $line) : self::plain($contents, $line);
        } catch (Throwable) {
            return []; // Source is optional, including when a file has moved or become unreadable.
        }
    }

    /**
     * @return array<int, string>
     */
    private static function plain(string $contents, int $line): array
    {
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $contents));

        if ($line > count($lines)) {
            return [];
        }

        $start = max(1, $line - self::PLAIN_CONTEXT_LINES);
        $end   = min(count($lines), $line + self::PLAIN_CONTEXT_LINES);

        $excerpt = [];

        for ($number = $start; $number <= $end; $number++) {
            $text = $lines[$number - 1];

            $excerpt[$number] = strlen($text) > self::MAX_LINE_BYTES
                ? mb_strcut($text, 0, self::MAX_LINE_BYTES, 'UTF-8') . '…'
                : $text;
        }

        return $excerpt;
    }

    /**
     * Highlight the requested lines using the full file's token context.
     *
     * @return array<int, string>
     */
    private static function highlight(string $contents, int $line): array
    {
        $start = max(1, $line - self::CONTEXT_LINES);
        $end   = $line + self::CONTEXT_LINES;

        $current = 1;
        $bytes   = 0;
        $lines   = [];

        foreach (token_get_all(str_replace(["\r\n", "\r"], "\n", $contents)) as $token) {
            $text  = is_array($token) ? $token[1] : $token;
            $class = match (is_array($token) ? $token[0] : null) {
                T_COMMENT, T_DOC_COMMENT                              => 'comment',
                T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE => 'string',
                T_INLINE_HTML, T_STRING, T_VARIABLE, T_WHITESPACE,
                T_LNUMBER, T_DNUMBER, T_NAME_QUALIFIED,
                T_NAME_FULLY_QUALIFIED, T_NAME_RELATIVE => '',
                default                                 => 'keyword',
            };

            foreach (explode("\n", $text) as $index => $part) {
                if ($index > 0) {
                    $current++;
                    $bytes = 0;
                }

                if ($current > $end) {
                    return $lines;
                }

                if ($current < $start) {
                    continue;
                }

                $lines[$current] ??= '';
                $remaining = self::MAX_LINE_BYTES - $bytes;

                if ($part === '' || $remaining < 0) {
                    continue;
                }

                $length = strlen($part);
                $slice  = esc(mb_strcut($part, 0, $remaining, 'UTF-8'));

                if ($slice !== '') {
                    $lines[$current] .= $class === ''
                        ? $slice
                        : sprintf('<span class="php-%s">%s</span>', $class, $slice);
                }

                if ($length > $remaining) {
                    $lines[$current] .= '…';
                }

                $bytes += $length;
            }
        }

        return isset($lines[$line]) ? $lines : [];
    }
}
