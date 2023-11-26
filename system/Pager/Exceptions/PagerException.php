<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Pager\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

class PagerException extends FrameworkException
{
    /**
     * Throws when the template is invalid.
     *
     * @return static
     */
    public static function forInvalidTemplate(?string $template = null)
    {
        return new static(lang('Pager.invalidTemplate', [$template]));
    }

    /**
     * Throws when the group is invalid.
     *
     * @return static
     */
    public static function forInvalidPaginationGroup(?string $group = null)
    {
        return new static(lang('Pager.invalidPaginationGroup', [$group]));
    }
}
