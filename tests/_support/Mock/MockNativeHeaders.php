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

use CodeIgniter\Test\Utilities\NativeHeadersStack;

/**
 * Mock implementation of the native PHP `headers_sent()` function.
 *
 * Instead of checking the actual PHP output buffer, this function
 * checks the static property in NativeHeadersStack.
 *
 * @return bool True if headers are considered sent, false otherwise.
 */
function headers_sent(): bool
{
    return NativeHeadersStack::$headersSent;
}

/**
 * Mock implementation of the native PHP `headers_list()` function.
 *
 * Retrieves the array of headers stored in the NativeHeadersStack class
 * rather than the actual headers sent by the server.
 *
 * @return array The list of simulated headers.
 */
function headers_list(): array
{
    return NativeHeadersStack::$headers;
}
