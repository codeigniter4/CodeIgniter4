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

if (! function_exists('is_cli')) {
    /**
     * Is CLI?
     *
     * Test to see if a request was made from the command line.
     * You can set the return value for testing.
     *
     * @param bool $newReturn return value to set
     */
    function is_cli(?bool $newReturn = null): bool
    {
        // PHPUnit always runs via CLI.
        static $returnValue = true;

        if ($newReturn !== null) {
            $returnValue = $newReturn;
        }

        return $returnValue;
    }
}
