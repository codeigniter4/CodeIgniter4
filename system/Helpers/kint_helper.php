<?php

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
         * @param array ...$vars
         *
         * @codeCoverageIgnore Can't be tested ... exits
         */
        function dd(...$vars)
        {
            // @codeCoverageIgnoreStart
            Kint::$aliases[] = 'dd';
            Kint::dump(...$vars);

            exit;
            // @codeCoverageIgnoreEnd
        }
    } else {
        // In case that Kint is not loaded.
        function dd(...$vars)
        {
            return 0;
        }
    }
}

if (! function_exists('d') && ! class_exists(Kint::class)) {
    // In case that Kint is not loaded.
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
        function trace()
        {
            Kint::$aliases[] = 'trace';
            Kint::trace();
        }
    } else {
        // In case that Kint is not loaded.
        function trace()
        {
            return 0;
        }
    }
}
