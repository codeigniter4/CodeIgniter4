<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Entity\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

/**
 * CastException is thrown for invalid cast initialization and management.
 */
class CastException extends FrameworkException
{
    /**
     * Thrown when the cast class does not extends BaseCast.
     *
     * @return static
     */
    public static function forInvalidInterface(string $class)
    {
        return new static(lang('Cast.baseCastMissing', [$class]));
    }

    /**
     * Thrown when the Json format is invalid.
     *
     * @return static
     */
    public static function forInvalidJsonFormat(int $error)
    {
        switch ($error) {
            case JSON_ERROR_DEPTH:
                return new static(lang('Cast.jsonErrorDepth'));

            case JSON_ERROR_STATE_MISMATCH:
                return new static(lang('Cast.jsonErrorStateMismatch'));

            case JSON_ERROR_CTRL_CHAR:
                return new static(lang('Cast.jsonErrorCtrlChar'));

            case JSON_ERROR_SYNTAX:
                return new static(lang('Cast.jsonErrorSyntax'));

            case JSON_ERROR_UTF8:
                return new static(lang('Cast.jsonErrorUtf8'));

            default:
                return new static(lang('Cast.jsonErrorUnknown'));
        }
    }

    /**
     * Thrown when the cast method is not `get` or `set`.
     *
     * @return static
     */
    public static function forInvalidMethod(string $method)
    {
        return new static(lang('Cast.invalidCastMethod', [$method]));
    }

    /**
     * Thrown when the casting timestamp is not correct timestamp.
     *
     * @return static
     */
    public static function forInvalidTimestamp()
    {
        return new static(lang('Cast.invalidTimestamp'));
    }
}
