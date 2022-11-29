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
 * Cast Exceptions.
 *
 * @deprecated use CodeIgniter\Entity\Exceptions\CastException instead.
 *
 * @codeCoverageIgnore
 */
class CastException extends CriticalError implements HasExitCodeInterface
{
    use DebugTraceableTrait;

    public function getExitCode(): int
    {
        return EXIT_CONFIG;
    }

    public static function forInvalidJsonFormatException(int $error)
    {
        return match ($error) {
            JSON_ERROR_DEPTH          => new static(lang('Cast.jsonErrorDepth')),
            JSON_ERROR_STATE_MISMATCH => new static(lang('Cast.jsonErrorStateMismatch')),
            JSON_ERROR_CTRL_CHAR      => new static(lang('Cast.jsonErrorCtrlChar')),
            JSON_ERROR_SYNTAX         => new static(lang('Cast.jsonErrorSyntax')),
            JSON_ERROR_UTF8           => new static(lang('Cast.jsonErrorUtf8')),
            default                   => new static(lang('Cast.jsonErrorUnknown')),
        };
    }
}
