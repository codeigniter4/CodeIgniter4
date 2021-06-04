<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Router\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

/**
 * RouterException
 */
class RouterException extends FrameworkException
{
    /**
     * Thrown when the actual parameter type does not match
     * the expected types.
     *
     * @return RouterException
     */
    public static function forInvalidParameterType()
    {
        return new static(lang('Router.invalidParameterType'));
    }

    /**
     * Thrown when a default route is not set.
     *
     * @return RouterException
     */
    public static function forMissingDefaultRoute()
    {
        return new static(lang('Router.missingDefaultRoute'));
    }

    /**
     * Throw when controller or its method is not found.
     *
     * @param string $controller
     * @param string $method
     *
     * @return RouterException
     */
    public static function forControllerNotFound(string $controller, string $method)
    {
        return new static(lang('HTTP.controllerNotFound', [$controller, $method]));
    }

    /**
     * Throw when route is not valid.
     *
     * @param string $route
     *
     * @return RouterException
     */
    public static function forInvalidRoute(string $route)
    {
        return new static(lang('HTTP.invalidRoute', [$route]));
    }
}
