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

namespace CodeIgniter\Router;

/**
 * Expected behavior of a AutoRouter.
 */
interface AutoRouterInterface
{
    /**
     * Returns the directory name, controller name, controller method, and any parameters for the given URI and HTTP verb.
     *
     * @param string $httpVerb HTTP verb like `GET`,`POST`
     *
     * @return array{string|null, string, string, list<string>}
     */
    public function getRoute(string $uri, string $httpVerb): array;
}
