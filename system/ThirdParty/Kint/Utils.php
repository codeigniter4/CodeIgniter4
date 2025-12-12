<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2013 Jonathan Vollebregt (jnvsor@gmail.com), Rokas Šleinius (raveren@gmail.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Kint;

use Kint\Value\StringValue;
use Kint\Value\TraceFrameValue;
use ReflectionNamedType;
use ReflectionType;
use UnexpectedValueException;

/**
 * A collection of utility methods. Should all be static methods with no dependencies.
 *
 * @psalm-import-type Encoding from StringValue
 * @psalm-import-type TraceFrame from TraceFrameValue
 */
final class Utils
{
    public const BT_STRUCTURE = [
        'function' => 'string',
        'line' => 'integer',
        'file' => 'string',
        'class' => 'string',
        'object' => 'object',
        'type' => 'string',
        'args' => 'array',
    ];

    public const BYTE_UNITS = ['B', 'KB', 'MB', 'GB', 'TB'];

    /**
     * @var array Character encodings to detect
     *
     * @see https://secure.php.net/function.mb-detect-order
     *
     * In practice, mb_detect_encoding can only successfully determine the
     * difference between the following common charsets at once without
     * breaking things for one of the other charsets:
     * - ASCII
     * - UTF-8
     * - SJIS
     * - EUC-JP
     *
     * The order of the charsets is significant. If you put UTF-8 before ASCII
     * it will never match ASCII, because UTF-8 is a superset of ASCII.
     * Similarly, SJIS and EUC-JP frequently match UTF-8 strings, so you should
     * check UTF-8 first. SJIS and EUC-JP seem to work either way, but SJIS is
     * more common so it should probably be first.
     *
     * While you're free to experiment with other charsets, remember to keep
     * this behavior in mind when setting up your char_encodings array.
     *
     * This depends on the mbstring extension
     */
    public static array $char_encodings = [
        'ASCII',
        'UTF-8',
    ];

    /**
     * @var array Legacy character encodings to detect
     *
     * @see https://secure.php.net/function.iconv
     *
     * Assuming the other encoding checks fail, this will perform a
     * simple iconv conversion to check for invalid bytes. If any are
     * found it will not match.
     *
     * This can be useful for ambiguous single byte encodings like
     * windows-125x and iso-8859-x which have practically undetectable
     * differences because they use every single byte available.
     *
     * This is *NOT* reliable and should not be trusted implicitly. Since it
     * works by triggering and suppressing conversion warnings, your error
     * handler may complain.
     *
     * As with char_encodings, the order of the charsets is significant.
     *
     * This depends on the iconv extension
     */
    public static array $legacy_encodings = [];

    /**
     * @var array Path aliases that will be displayed instead of the full path.
     *
     * Keys are paths, values are replacement strings
     *
     * Example for laravel:
     *
     * Utils::$path_aliases = [
     *     base_path() => '<BASE>',
     *     app_path() => '<APP>',
     *     base_path().'/vendor' => '<VENDOR>',
     * ];
     *
     * Defaults to [$_SERVER['DOCUMENT_ROOT'] => '<ROOT>']
     *
     * @psalm-var array<non-empty-string, string>
     */
    public static array $path_aliases = [];

    /**
     * @codeCoverageIgnore
     *
     * @psalm-suppress UnusedConstructor
     */
    private function __construct()
    {
    }

    /**
     * Turns a byte value into a human-readable representation.
     *
     * @param int $value Amount of bytes
     *
     * @return array Human readable value and unit
     *
     * @psalm-return array{value: float, unit: 'B'|'KB'|'MB'|'GB'|'TB'}
     *
     * @psalm-pure
     */
    public static function getHumanReadableBytes(int $value): array
    {
        $negative = $value < 0;
        $value = \abs($value);

        if ($value < 1024) {
            $i = 0;
            $value = \floor($value);
        } elseif ($value < 0xFFFCCCCCCCCCCCC >> 40) {
            $i = 1;
        } elseif ($value < 0xFFFCCCCCCCCCCCC >> 30) {
            $i = 2;
        } elseif ($value < 0xFFFCCCCCCCCCCCC >> 20) {
            $i = 3;
        } else {
            $i = 4;
        }

        if ($i) {
            $value = $value / \pow(1024, $i);
        }

        if ($negative) {
            $value *= -1;
        }

        return [
            'value' => \round($value, 1),
            'unit' => self::BYTE_UNITS[$i],
        ];
    }

    /** @psalm-pure */
    public static function isSequential(array $array): bool
    {
        return \array_keys($array) === \range(0, \count($array) - 1);
    }

    /** @psalm-pure */
    public static function isAssoc(array $array): bool
    {
        return (bool) \count(\array_filter(\array_keys($array), 'is_string'));
    }

    /**
     * @psalm-assert-if-true list<TraceFrame> $trace
     */
    public static function isTrace(array $trace): bool
    {
        if (!self::isSequential($trace)) {
            return false;
        }

        $file_found = false;

        foreach ($trace as $frame) {
            if (!\is_array($frame) || !isset($frame['function'])) {
                return false;
            }

            if (isset($frame['class']) && !\class_exists($frame['class'], false)) {
                return false;
            }

            foreach ($frame as $key => $val) {
                if (!isset(self::BT_STRUCTURE[$key])) {
                    return false;
                }

                if (\gettype($val) !== self::BT_STRUCTURE[$key]) {
                    return false;
                }

                if ('file' === $key) {
                    $file_found = true;
                }
            }
        }

        return $file_found;
    }

    /**
     * @psalm-param TraceFrame $frame
     *
     * @psalm-pure
     */
    public static function traceFrameIsListed(array $frame, array $matches): bool
    {
        if (isset($frame['class'])) {
            $called = [\strtolower($frame['class']), \strtolower($frame['function'])];
        } else {
            $called = \strtolower($frame['function']);
        }

        return \in_array($called, $matches, true);
    }

    /** @psalm-pure */
    public static function normalizeAliases(array $aliases): array
    {
        foreach ($aliases as $index => $alias) {
            if (\is_array($alias) && 2 === \count($alias)) {
                $alias = \array_values(\array_filter($alias, 'is_string'));

                if (2 === \count($alias) && self::isValidPhpName($alias[1]) && self::isValidPhpNamespace($alias[0])) {
                    $aliases[$index] = [
                        \strtolower(\ltrim($alias[0], '\\')),
                        \strtolower($alias[1]),
                    ];
                } else {
                    unset($aliases[$index]);
                    continue;
                }
            } elseif (\is_string($alias)) {
                if (self::isValidPhpNamespace($alias)) {
                    $alias = \explode('\\', \strtolower($alias));
                    $aliases[$index] = \end($alias);
                } else {
                    unset($aliases[$index]);
                    continue;
                }
            } else {
                unset($aliases[$index]);
            }
        }

        return \array_values($aliases);
    }

    /** @psalm-pure */
    public static function isValidPhpName(string $name): bool
    {
        return (bool) \preg_match('/^[a-zA-Z_\\x80-\\xff][a-zA-Z0-9_\\x80-\\xff]*$/', $name);
    }

    /** @psalm-pure */
    public static function isValidPhpNamespace(string $ns): bool
    {
        $parts = \explode('\\', $ns);
        if ('' === \reset($parts)) {
            \array_shift($parts);
        }

        if (!\count($parts)) {
            return false;
        }

        foreach ($parts as $part) {
            if (!self::isValidPhpName($part)) {
                return false;
            }
        }

        return true;
    }

    /**
     * trigger_error before PHP 8.1 truncates the error message at nul
     * so we have to sanitize variable strings before using them.
     *
     * @psalm-pure
     */
    public static function errorSanitizeString(string $input): string
    {
        if (KINT_PHP82 || '' === $input) {
            return $input;
        }

        return (string) \strtok($input, "\0"); // @codeCoverageIgnore
    }

    /** @psalm-pure */
    public static function getTypeString(ReflectionType $type): string
    {
        // @codeCoverageIgnoreStart
        // ReflectionType::__toString was deprecated in 7.4 and undeprecated in 8
        // and toString doesn't correctly show the nullable ? in the type before 8
        if (!KINT_PHP80) {
            if (!$type instanceof ReflectionNamedType) {
                throw new UnexpectedValueException('ReflectionType on PHP 7 must be ReflectionNamedType');
            }

            $name = $type->getName();
            if ($type->allowsNull() && 'mixed' !== $name && false === \strpos($name, '|')) {
                $name = '?'.$name;
            }

            return $name;
        }
        // @codeCoverageIgnoreEnd

        return (string) $type;
    }

    /**
     * @psalm-param Encoding $encoding
     */
    public static function truncateString(string $input, int $length = PHP_INT_MAX, string $end = '...', $encoding = false): string
    {
        $endlength = self::strlen($end);

        if ($endlength >= $length) {
            $endlength = 0;
            $end = '';
        }

        if (self::strlen($input, $encoding) > $length) {
            return self::substr($input, 0, $length - $endlength, $encoding).$end;
        }

        return $input;
    }

    /**
     * @psalm-return Encoding
     */
    public static function detectEncoding(string $string)
    {
        if (\function_exists('mb_detect_encoding')) {
            $ret = \mb_detect_encoding($string, self::$char_encodings, true);
            if (false !== $ret) {
                return $ret;
            }
        }

        // Pretty much every character encoding uses first 32 bytes as control
        // characters. If it's not a multi-byte format it's safe to say matching
        // any control character besides tab, nl, and cr means it's binary.
        if (\preg_match('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/', $string)) {
            return false;
        }

        if (\function_exists('iconv')) {
            foreach (self::$legacy_encodings as $encoding) {
                // Iconv detection works by triggering
                // "Detected an illegal character in input string" notices
                // This notice does not become a TypeError with strict_types
                // so we don't have to wrap this in a try catch
                if (@\iconv($encoding, $encoding, $string) === $string) {
                    return $encoding;
                }
            }
        } elseif (!\function_exists('mb_detect_encoding')) { // @codeCoverageIgnore
            // If a user has neither mb_detect_encoding, nor iconv, nor the
            // polyfills, there's not much we can do about it...
            // Pretend it's ASCII and pray the browser renders it properly.
            return 'ASCII'; // @codeCoverageIgnore
        }

        return false;
    }

    /**
     * @psalm-param Encoding $encoding
     */
    public static function strlen(string $string, $encoding = false): int
    {
        if (\function_exists('mb_strlen')) {
            if (false === $encoding) {
                $encoding = self::detectEncoding($string);
            }

            if (false !== $encoding && 'ASCII' !== $encoding) {
                return \mb_strlen($string, $encoding);
            }
        }

        return \strlen($string);
    }

    /**
     * @psalm-param Encoding $encoding
     */
    public static function substr(string $string, int $start, ?int $length = null, $encoding = false): string
    {
        if (\function_exists('mb_substr')) {
            if (false === $encoding) {
                $encoding = self::detectEncoding($string);
            }

            if (false !== $encoding && 'ASCII' !== $encoding) {
                return \mb_substr($string, $start, $length, $encoding);
            }
        }

        // Special case for substr/mb_substr discrepancy
        if ('' === $string) {
            return '';
        }

        return (string) \substr($string, $start, $length ?? PHP_INT_MAX);
    }

    public static function shortenPath(string $file): string
    {
        $split = \explode('/', \str_replace('\\', '/', $file));

        $longest_match = 0;
        $match = '';

        foreach (self::$path_aliases as $path => $alias) {
            $path = \explode('/', \str_replace('\\', '/', $path));

            if (\count($path) < 2) {
                continue;
            }

            if (\array_slice($split, 0, \count($path)) === $path && \count($path) > $longest_match) {
                $longest_match = \count($path);
                $match = $alias;
            }
        }

        if ($longest_match) {
            $suffix = \implode('/', \array_slice($split, $longest_match));

            if (\preg_match('%^/*$%', $suffix)) {
                return $match;
            }

            return $match.'/'.$suffix;
        }

        // fallback to find common path with Kint dir
        $kint = \explode('/', \str_replace('\\', '/', KINT_DIR));
        $had_real_path_part = false;

        foreach ($split as $i => $part) {
            if (!isset($kint[$i]) || $kint[$i] !== $part) {
                if (!$had_real_path_part) {
                    break;
                }

                $suffix = \implode('/', \array_slice($split, $i));

                if (\preg_match('%^/*$%', $suffix)) {
                    break;
                }

                $prefix = $i > 1 ? '.../' : '/';

                return $prefix.$suffix;
            }

            if ($i > 0 && \strlen($kint[$i])) {
                $had_real_path_part = true;
            }
        }

        return $file;
    }

    public static function composerGetExtras(string $key = 'kint'): array
    {
        if (0 === \strpos(KINT_DIR, 'phar://')) {
            // Only run inside phar file, so skip for code coverage
            return []; // @codeCoverageIgnore
        }

        $extras = [];

        $folder = KINT_DIR.'/vendor';

        for ($i = 0; $i < 4; ++$i) {
            $installed = $folder.'/composer/installed.json';

            if (\file_exists($installed) && \is_readable($installed)) {
                $packages = \json_decode((string) \file_get_contents($installed), true);

                if (!\is_array($packages)) {
                    continue;
                }

                // Composer 2.0 Compatibility: packages are now wrapped into a "packages" top level key instead of the whole file being the package array
                // @see https://getcomposer.org/upgrade/UPGRADE-2.0.md
                foreach ($packages['packages'] ?? $packages as $package) {
                    if (\is_array($package['extra'][$key] ?? null)) {
                        $extras = \array_replace($extras, $package['extra'][$key]);
                    }
                }

                $folder = \dirname($folder);

                if (\file_exists($folder.'/composer.json') && \is_readable($folder.'/composer.json')) {
                    $composer = \json_decode((string) \file_get_contents($folder.'/composer.json'), true);

                    if (\is_array($composer['extra'][$key] ?? null)) {
                        $extras = \array_replace($extras, $composer['extra'][$key]);
                    }
                }

                break;
            }

            $folder = \dirname($folder);
        }

        return $extras;
    }

    /**
     * @codeCoverageIgnore
     */
    public static function composerSkipFlags(): void
    {
        if (\defined('KINT_SKIP_FACADE') && \defined('KINT_SKIP_HELPERS')) {
            return;
        }

        $extras = self::composerGetExtras();

        if (!empty($extras['disable-facade']) && !\defined('KINT_SKIP_FACADE')) {
            \define('KINT_SKIP_FACADE', true);
        }

        if (!empty($extras['disable-helpers']) && !\defined('KINT_SKIP_HELPERS')) {
            \define('KINT_SKIP_HELPERS', true);
        }
    }
}
