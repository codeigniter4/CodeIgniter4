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

// This helper is autoloaded by CodeIgniter.

if (! function_exists('dd')) {
    if (class_exists(Kint::class)) {
        /**
         * Prints a Kint debug report and exits.
         *
         * @param array $vars
         *
         * @return never
         *
         * @codeCoverageIgnore Can't be tested ... exits
         */
        function dd(...$vars): void
        {
            // @codeCoverageIgnoreStart
            Kint::$aliases[] = 'dd';
            Kint::dump(...$vars);

            exit;
            // @codeCoverageIgnoreEnd
        }
    } else {
        // In case that Kint is not loaded.
        /**
         * dd function
         *
         * @param array $vars
         *
         * @return int
         */
        function dd(...$vars)
        {
            return 0;
        }
    }
}

if (! function_exists('d') && ! class_exists(Kint::class)) {
    // In case that Kint is not loaded.
    /**
     * d function
     *
     * @param array $vars
     *
     * @return int
     */
    function d(...$vars)
    {
        return 0;
    }
}

if (! function_exists('trace')) {
    if (class_exists(Kint::class)) {
        /**
         * Provides a backtrace to the current execution point, from Kint.
         */
        /**
         * trace function
         */
        function trace(): void
        {
            Kint::$aliases[] = 'trace';
            Kint::trace();
        }
    } else {
        // In case that Kint is not loaded.
        /**
         * trace function
         *
         * @return int
         */
        function trace()
        {
            return 0;
        }
    }
}
