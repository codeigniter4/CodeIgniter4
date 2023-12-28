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

namespace CodeIgniter\Test;

use Config\Services;

/**
 * Provides utilities for registering and unregistering
 * of the exception and error handlers.
 */
trait ExceptionHandlingTrait
{
    protected function enableExceptionHandling(): void
    {
        Services::exceptions()->register();
    }

    protected function disableExceptionHandling(): void
    {
        Services::exceptions()->unregister();
    }
}
