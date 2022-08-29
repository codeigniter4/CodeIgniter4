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

namespace CodeIgniter\Exceptions;

/**
 * Interface for Exceptions that has exception code as exit code.
 */
interface HasExitCodeInterface
{
    /**
     * Returns exit status code.
     */
    public function getExitCode(): int;
}
