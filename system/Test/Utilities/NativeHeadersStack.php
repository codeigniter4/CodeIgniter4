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
 * A utility class for simulating native PHP header handling in unit tests.
 *
 * @internal This class is for testing purposes only.
 */
final class NativeHeadersStack
{
    /**
     * Simulates whether headers have been sent.
     */
    public static bool $headersSent = false;

    /**
     * Stores the list of headers.
     *
     * @var list<string>
     */
    public static array $headers = [];

    /**
     * Resets the header stack to defaults.
     * Call this in setUp() to ensure clean state between tests.
     */
    public static function reset(): void
    {
        self::$headersSent = false;
        self::$headers     = [];
    }

    /**
     * Checks if a specific header exists in the stack.
     *
     * @param string $header The exact header string (e.g., 'Content-Type: text/html')
     */
    public static function has(string $header): bool
    {
        return in_array($header, self::$headers, true);
    }

    /**
     * Adds a header to the stack.
     *
     * @param string $header The header to add (e.g., 'Content-Type: text/html')
     */
    public static function push(string $header): void
    {
        self::$headers[] = $header;
    }
}
