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

/**
 * Class MockNativeHeaders
 *
 * This class serves as a container to hold the state of HTTP headers
 * during unit testing. It allows the framework to simulate sending headers
 * without actually outputting them to the CLI or browser.
 */
class MockNativeHeaders
{
    /**
     * Simulates the state of whether headers have been sent.
     */
    public static bool $headersSent = false;

    /**
     * Stores the list of headers that have been sent.
     */
    public static array $headers = [];

    /**
     * Resets the class state to defaults.
     * Useful for cleaning up between individual tests.
     */
    public static function reset(): void
    {
        self::$headersSent = false;
        self::$headers     = [];
    }
}

/**
 * Mock implementation of the native PHP headers_sent() function.
 *
 * Instead of checking the actual PHP output buffer, this function
 * checks the static property in MockNativeHeaders.
 *
 * @return bool True if headers are considered sent, false otherwise.
 */
function headers_sent(): bool
{
    return MockNativeHeaders::$headersSent;
}

/**
 * Mock implementation of the native PHP headers_list() function.
 *
 * Retrieves the array of headers stored in the MockNativeHeaders class
 * rather than the actual headers sent by the server.
 *
 * @return array The list of simulated headers.
 */
function headers_list(): array
{
    return MockNativeHeaders::$headers;
}
