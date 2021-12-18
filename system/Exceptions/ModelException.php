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
 * Model Exceptions.
 */
class ModelException extends FrameworkException
{
    public static function forNoPrimaryKey(string $modelName)
    {
        return new static(lang('Database.noPrimaryKey', [$modelName]));
    }

    public static function forNoDateFormat(string $modelName)
    {
        return new static(lang('Database.noDateFormat', [$modelName]));
    }
}
