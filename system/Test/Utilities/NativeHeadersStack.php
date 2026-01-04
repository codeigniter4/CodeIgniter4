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

namespace CodeIgniter\Test\Utilities;

/**
 * Class NativeHeadersStack
 *
 * A utility class for simulating native PHP header handling in unit tests.
 * It allows the inspection, manipulation, and mocking of HTTP headers without
 * affecting the actual HTTP output.
 *
 * @internal This class is for testing purposes only.
 */
final class NativeHeadersStack
{
    private static bool $headersSent = false;

    /**
     * @var array<string, list<string>>
     */
    private static array $headers = [];

    private static ?int $responseCode = null;

    /**
     * Resets the state of the class to its default values.
     */
    public static function reset(): void
    {
        self::$headersSent  = false;
        self::$headers      = [];
        self::$responseCode = null;
    }

    /**
     * Sets the state of whether headers have been sent.
     */
    public static function setHeadersSent(bool $sent): void
    {
        self::$headersSent = $sent;
    }

    /**
     * Simulates PHP's native `headers_sent()` function.
     */
    public static function headersSent(): bool
    {
        return self::$headersSent;
    }

    /**
     * Sets a header by name, replacing or appending it.
     * This is the main method for header manipulation.
     *
     * @param string   $header       The header string (e.g., 'Content-Type: application/json').
     * @param bool     $replace      Whether to replace a previous similar header.
     * @param int|null $responseCode Forces the HTTP response code to the specified value.
     */
    public static function set(string $header, bool $replace = true, ?int $responseCode = null): void
    {
        if (str_contains($header, ':')) {
            [$name, $value] = explode(':', $header, 2);
            $name           = trim($name);
            $value          = trim($value);

            if ($replace || ! isset(self::$headers[strtolower($name)])) {
                self::$headers[strtolower($name)] = [];
            }
            self::$headers[strtolower($name)][] = "{$name}: {$value}";
        } else {
            // Handle non-key-value headers like "HTTP/1.1 404 Not Found"
            self::$headers['status'][] = $header;
        }

        if ($responseCode !== null) {
            self::$responseCode = $responseCode;
        }
    }

    /**
     * Pushes a header to the stack without replacing existing ones.
     */
    public static function push(string $header): void
    {
        self::set($header, false);
    }

    /**
     * A convenience method to push multiple headers at once.
     *
     * @param list<string> $headers An array of headers to push onto the stack.
     */
    public static function pushMany(array $headers): void
    {
        foreach ($headers as $header) {
            // Default to not replacing for multiple adds
            self::set($header, false);
        }
    }

    /**
     * Simulates PHP's `headers_list()` function.
     *
     * @return list<string> The list of simulated headers.
     */
    public static function listHeaders(): array
    {
        $list = [];

        foreach (self::$headers as $values) {
            $list = array_merge($list, $values);
        }

        return $list;
    }

    /**
     * Checks if a header with the given name exists in the stack (case-insensitive).
     *
     * @param string $name The header name to search for (e.g., 'Content-Type').
     */
    public static function hasHeader(string $name): bool
    {
        return isset(self::$headers[strtolower($name)]);
    }

    /**
     * Simulates PHP's `http_response_code()` function.
     *
     * @return int|null The stored response code, or null if not set.
     */
    public static function getResponseCode(): ?int
    {
        return self::$responseCode;
    }
}
