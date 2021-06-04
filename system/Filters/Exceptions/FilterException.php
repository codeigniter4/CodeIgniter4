<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Filters\Exceptions;

use CodeIgniter\Exceptions\ConfigException;
use CodeIgniter\Exceptions\ExceptionInterface;

/**
 * FilterException
 */
class FilterException extends ConfigException implements ExceptionInterface
{
    /**
     * Thrown when the provided alias is not within
     * the list of configured filter aliases.
     *
     * @param string $alias
     *
     * @return static
     */
    public static function forNoAlias(string $alias)
    {
        return new static(lang('Filters.noFilter', [$alias]));
    }

    /**
     * Thrown when the filter class does not implement FilterInterface.
     *
     * @param string $class
     *
     * @return static
     */
    public static function forIncorrectInterface(string $class)
    {
        return new static(lang('Filters.incorrectInterface', [$class]));
    }
}
