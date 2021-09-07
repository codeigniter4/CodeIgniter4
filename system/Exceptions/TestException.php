<?php

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
 * Exception for automatic logging.
 */
class TestException extends CriticalError
{
    use DebugTraceableTrait;

    public static function forInvalidMockClass(string $name)
    {
        return new static(lang('Test.invalidMockClass', [$name]));
    }
}
