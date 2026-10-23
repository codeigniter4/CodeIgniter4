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

namespace CodeIgniter\CLI;

/**
 * Verbosity level of the console output, valued as `SHELL_VERBOSITY`.
 */
enum Verbosity: int
{
    /**
     * No output at all, not even errors.
     */
    case Silent = -2;

    /**
     * Errors only.
     */
    case Quiet = -1;

    /**
     * The default output.
     */
    case Normal = 0;

    /**
     * Extra detail, as with `-v`.
     */
    case Verbose = 1;

    /**
     * More detail, as with `-vv`.
     */
    case VeryVerbose = 2;

    /**
     * Everything, including debug messages, as with `-vvv`.
     */
    case Debug = 3;
}
