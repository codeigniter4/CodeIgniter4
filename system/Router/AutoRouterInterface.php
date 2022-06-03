<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Router;

/**
 * Expected behavior of a AutoRouter.
 */
interface AutoRouterInterface
{
    /**
     * Returns controller, method and params from the URI.
     *
     * @return array [directory_name, controller_name, controller_method, params]
     */
    public function getRoute(string $uri): array;
}
