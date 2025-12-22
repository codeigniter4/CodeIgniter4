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

namespace Tests\Support;

/**
 * MockCommon.php
 *
 * Shared mocks and helpers for all tests in CodeIgniter 4.
 * Loaded automatically during `Boot::bootTest()`.
 *
 * Purpose:
 *  - Provide global mock functions like `is_cli()`
 *  - Keep tests isolated and maintainable
 *
 * How to extend:
 *  - Add new helper functions below
 *  - Keep mocks idempotent to avoid side effects
 */

// ---------------------------------------------------
// Environment helpers
// ---------------------------------------------------
if (! function_exists('is_cli')) {
    /**
     * Force non-CLI environment for tests
     */
    function is_cli(): bool
    {
        return false;
    }
}

// ---------------------------------------------------
// Placeholder for additional mocks/helpers
// ---------------------------------------------------
// Add any other test helpers here, e.g., logger mocks, cache mocks, etc.
