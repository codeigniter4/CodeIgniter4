<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Log\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

class LogException extends FrameworkException
{
    /**
     * @return static
     */
    public static function forInvalidLogLevel(string $level)
    {
        return new static(lang('Log.invalidLogLevel', [$level]));
    }

    /**
     * @return static
     */
    public static function forInvalidMessageType(string $messageType)
    {
        return new static(lang('Log.invalidMessageType', [$messageType]));
    }
}
