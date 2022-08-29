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

namespace CodeIgniter\View\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

class ViewException extends FrameworkException
{
    /**
     * @return static
     */
    public static function forInvalidCellMethod(string $class, string $method)
    {
        return new static(lang('View.invalidCellMethod', ['class' => $class, 'method' => $method]));
    }

    /**
     * @return static
     */
    public static function forMissingCellParameters(string $class, string $method)
    {
        return new static(lang('View.missingCellParameters', ['class' => $class, 'method' => $method]));
    }

    /**
     * @return static
     */
    public static function forInvalidCellParameter(string $key)
    {
        return new static(lang('View.invalidCellParameter', [$key]));
    }

    /**
     * @return static
     */
    public static function forNoCellClass()
    {
        return new static(lang('View.noCellClass'));
    }

    /**
     * @return static
     */
    public static function forInvalidCellClass(?string $class = null)
    {
        return new static(lang('View.invalidCellClass', [$class]));
    }

    /**
     * @return static
     */
    public static function forTagSyntaxError(string $output)
    {
        return new static(lang('View.tagSyntaxError', [$output]));
    }

    /**
     * @return static
     */
    public static function forInvalidDecorator(string $className)
    {
        return new static(lang('View.invalidDecoratorClass', [$className]));
    }
}
