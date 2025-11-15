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

use Throwable;

/**
 * This trait provides framework exceptions the ability to pinpoint
 * accurately where the exception was raised rather than instantiated.
 *
 * This is used primarily for factory-instantiated exceptions.
 */
trait DebugTraceableTrait
{
    /**
     * Tweaks the exception's constructor to assign the file/line to where
     * it is actually raised rather than were it is instantiated.
     */
    final public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $trace = $this->getTrace()[0];

        if (isset($trace['class']) && $trace['class'] === static::class) {
            [
                'line' => $this->line,
                'file' => $this->file,
            ] = $trace;
        }
    }
}
