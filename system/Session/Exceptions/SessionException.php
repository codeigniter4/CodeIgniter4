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

namespace CodeIgniter\Session\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

class SessionException extends FrameworkException
{
    /**
     * @return static
     */
    public static function forMissingDatabaseTable()
    {
        return new static(lang('Session.missingDatabaseTable'));
    }

    /**
     * @return static
     */
    public static function forInvalidSavePath(?string $path = null)
    {
        return new static(lang('Session.invalidSavePath', [$path]));
    }

    /**
     * @return static
     */
    public static function forWriteProtectedSavePath(?string $path = null)
    {
        return new static(lang('Session.writeProtectedSavePath', [$path]));
    }

    /**
     * @return static
     */
    public static function forEmptySavepath()
    {
        return new static(lang('Session.emptySavePath'));
    }

    /**
     * @return static
     */
    public static function forInvalidSavePathFormat(string $path)
    {
        return new static(lang('Session.invalidSavePathFormat', [$path]));
    }

    /**
     * @deprecated
     *
     * @return static
     *
     * @codeCoverageIgnore
     */
    public static function forInvalidSameSiteSetting(string $samesite)
    {
        return new static(lang('Session.invalidSameSiteSetting', [$samesite]));
    }
}
