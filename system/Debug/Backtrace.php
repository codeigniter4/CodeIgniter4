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
use CodeIgniter\Debug\Inspection\Frames;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionReference;
use SensitiveParameterValue;

/**
 * Formats backtraces for display and logging.
 *
 * @internal
 */
final class Backtrace
{
    /**
     * Display text for a masked value.
     */
    public const REDACTED_TEXT = '[redacted]';

    /**
     * Maximum arguments shown per call.
     */
    private const MAX_ARGUMENTS = 20;

    /**
     * Maximum items shown per array.
     */
    private const MAX_ARRAY_ITEMS = 8;

    /**
     * Maximum array nesting expanded in an argument.
     */
    private const MAX_ARRAY_DEPTH = 3;

    /**
     * Total values visited per argument, including nested items.
     */
    private const MAX_ARGUMENT_VALUES = 32;

    /**
     * Byte limit for a formatted scalar before the ellipsis.
     */
    private const MAX_VALUE_BYTES = 500;

    /**
     * Parameter names and array keys masked by default.
     */
    private const SENSITIVE_ARGUMENTS = [
        'password',
        'password_confirmation',
        'token',
        'access_token',
        'refresh_token',
        'secret',
        'client_secret',
        'apikey',
        'api_key',
        'dsn',
        'authorization',
        'proxy-authorization',
        'cookie',
        'set-cookie',
        'x-api-key',
        'x-auth-token',
        'x-csrf-token',
        'x-xsrf-token',
        'http_authorization',
        'http_proxy_authorization',
        'http_cookie',
        'php_auth_pw',
    ];

    /**
     * Format a call with bounded, redacted arguments.
     *
     * @param list<string> $sensitiveKeys
     */
    public static function call(Frame $frame, array $sensitiveKeys = []): string
    {
        $arguments = self::formatArguments($frame, $sensitiveKeys, multiline: false);
        $hidden    = count($frame->arguments) - count($arguments);

        if ($hidden > 0) {
            $arguments[] = sprintf('… +%d more', $hidden);
        }

        return $frame->call(implode(', ', $arguments));
    }

    /**
     * Format frames as numbered plain-text lines for logs and reports.
     *
     * @param list<string> $sensitiveKeys
     *
     * @return list<string>
     */
    public static function lines(Frames $trace, bool $includeArguments = true, array $sensitiveKeys = []): array
    {
        $lines = [];

        foreach ($trace as $index => $frame) {
            $lines[] = sprintf(
                '%2d %s %s',
                $index + 1,
                $includeArguments ? self::call($frame, $sensitiveKeys) : $frame->call(),
                self::location($frame->file, $frame->line),
            );
        }

        return $lines;
    }

    /**
     * Strip the project root from paths inside the project.
     */
    public static function relativePath(string $file): string
    {
        return str_starts_with($file, ROOTPATH) ? substr($file, strlen(ROOTPATH)) : $file;
    }

    /**
     * Format a source location for logs and reports or as path:line for terminal links.
     */
    public static function location(?string $file, ?int $line, bool $cli = false): string
    {
        if ($file === null || $file === '') {
            return '[internal function]';
        }

        $source = Frame::sourceLocation($file, $line);
        $path   = self::relativePath($source['file']);
        $line   = $source['line'] ?? null;

        if ($cli) {
            return $line === null ? $path : sprintf('%s:%d', $path, $line);
        }

        return $line === null ? sprintf('in %s', $path) : sprintf('in %s (line %d)', $path, $line);
    }

    /**
     * Format a bounded, redacted preview keyed by parameter name or position.
     *
     * @param list<string> $sensitiveKeys
     *
     * @return array<array-key, string>
     */
    public static function argumentPreview(Frame $frame, array $sensitiveKeys = []): array
    {
        return self::formatArguments($frame, $sensitiveKeys, multiline: true);
    }

    /**
     * @param list<string> $sensitiveKeys
     *
     * @return array<array-key, string>
     */
    private static function formatArguments(Frame $frame, array $sensitiveKeys, bool $multiline): array
    {
        if ($frame->arguments === []) {
            return [];
        }

        $keys       = [...self::SENSITIVE_ARGUMENTS, ...array_map(strtolower(...), $sensitiveKeys)];
        $parameters = self::parameters($frame);
        $formatted  = [];

        foreach (array_slice($frame->arguments, 0, self::MAX_ARGUMENTS, true) as $index => $value) {
            $name      = $parameters[$index] ?? null;
            $path      = self::argumentName($parameters, $index);
            $remaining = self::MAX_ARGUMENT_VALUES;
            $reference = ReflectionReference::fromArrayElement($frame->arguments, $index)?->getId();

            $formatted[$name?->getName() ?? $index] = self::formatValue(
                $value,
                $keys,
                $path,
                (string) $index,
                $multiline,
                $remaining,
                $reference,
            );
        }

        return $formatted;
    }

    /**
     * @param list<string>       $sensitiveKeys
     * @param array<string,true> $ancestors
     */
    private static function formatValue(
        mixed $value,
        array $sensitiveKeys,
        string $path,
        string $position,
        bool $multiline,
        int &$remaining,
        ?string $reference,
        int $depth = 0,
        array $ancestors = [],
    ): string {
        $remaining--;

        if (self::isSensitiveArgument($value, $sensitiveKeys, $path, $position)) {
            return self::REDACTED_TEXT;
        }

        if (! is_array($value)) {
            return self::formatScalar($value, $multiline);
        }

        if ($value === []) {
            return '[]';
        }

        if ($reference !== null) {
            if (isset($ancestors[$reference])) {
                return '*RECURSION*';
            }

            $ancestors[$reference] = true;
        }

        if ($depth >= self::MAX_ARRAY_DEPTH) {
            return '[...]';
        }

        $rows   = [];
        $list   = array_is_list($value);
        $indent = $multiline ? str_repeat('    ', $depth + 1) : '';

        foreach (array_slice($value, 0, self::MAX_ARRAY_ITEMS, true) as $key => $item) {
            if ($remaining === 0) {
                break;
            }

            $formatted = self::formatValue(
                $item,
                $sensitiveKeys,
                sprintf('%s/%s', $path, $key),
                sprintf('%s/%s', $position, $key),
                $multiline,
                $remaining,
                ReflectionReference::fromArrayElement($value, $key)?->getId(),
                $depth + 1,
                $ancestors,
            );
            $row    = $list ? $formatted : sprintf('%s => %s', self::formatScalar($key, $multiline), $formatted);
            $rows[] = $multiline ? sprintf('%s%s,', $indent, $row) : $row;
        }

        $hidden = count($value) - count($rows);

        if ($hidden > 0) {
            $rows[] = sprintf('%s… +%d more', $indent, $hidden);
        }

        if (! $multiline) {
            return sprintf('[%s]', implode(', ', $rows));
        }

        return sprintf("[\n%s\n%s]", implode("\n", $rows), str_repeat('    ', $depth));
    }

    /**
     * @return list<ReflectionParameter>
     */
    private static function parameters(Frame $frame): array
    {
        try {
            $reflection = $frame->class !== null
                ? new ReflectionMethod($frame->class, $frame->function)
                : new ReflectionFunction($frame->function);

            return $reflection->getParameters();
        } catch (ReflectionException) {
            return [];
        }
    }

    /**
     * @param list<ReflectionParameter> $parameters
     */
    private static function argumentName(array $parameters, int|string $index): string
    {
        $parameter = $parameters[$index] ?? null;
        $last      = $parameters === [] ? null : $parameters[array_key_last($parameters)];

        return $parameter?->getName() ?? ($last?->isVariadic() ? $last->getName() : (string) $index);
    }

    /**
     * @param list<string> $keys
     */
    private static function isSensitiveArgument(mixed $value, array $keys, string $path, string $position): bool
    {
        if ($value instanceof SensitiveParameterValue) {
            return true;
        }

        $path     = sprintf('/%s', strtolower($path));
        $position = sprintf('/%s', strtolower($position));

        foreach ($keys as $key) {
            $suffix = sprintf('/%s', $key);

            if (str_ends_with($path, $suffix) || str_ends_with($position, $suffix)) {
                return true;
            }
        }

        return is_string($value) && preg_match(
            '~(?:^|\s)[a-z][a-z0-9+.-]*://[^\s/@]+:[^\s/@]*@|(?:^|[;\s:])(?:password|passwd|pwd)\s*=~i',
            $value,
        ) === 1;
    }

    private static function formatScalar(mixed $value, bool $multiline): string
    {
        $truncated = is_string($value) && strlen($value) > self::MAX_VALUE_BYTES;

        if ($truncated) {
            $value = mb_strcut($value, 0, self::MAX_VALUE_BYTES, 'UTF-8');
        }

        $text = match (true) {
            is_object($value)   => sprintf('Object(%s)', $value::class),
            $value === null     => 'null',
            is_resource($value) => sprintf('resource (%s)', get_resource_type($value)),
            default             => var_export($value, true),
        };

        if (! $multiline) {
            $text = addcslashes($text, "\0..\37\177");
        }

        return $truncated || strlen($text) > self::MAX_VALUE_BYTES
            ? sprintf('%s…', mb_strcut($text, 0, self::MAX_VALUE_BYTES, 'UTF-8'))
            : $text;
    }
}
