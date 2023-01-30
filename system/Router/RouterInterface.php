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

use Closure;
use CodeIgniter\HTTP\Request;

/**
 * Expected behavior of a Router.
 */
interface RouterInterface
{
    /**
     * Stores a reference to the RouteCollection object.
     */
    public function __construct(RouteCollectionInterface $routes, ?Request $request = null);

    /**
     * Finds the controller method corresponding to the URI.
     *
     * @param string|null $uri URI path relative to baseURL
     *
     * @return Closure|string Controller classname or Closure
     */
    public function handle(?string $uri = null);

    /**
     * Returns the name of the matched controller.
     *
     * @return Closure|string Controller classname or Closure
     */
    public function controllerName();

    /**
     * Returns the name of the method in the controller to run.
     *
     * @return string
     */
    public function methodName();

    /**
     * Returns the binds that have been matched and collected
     * during the parsing process as an array, ready to send to
     * instance->method(...$params).
     *
     * @return array
     */
    public function params();

    /**
     * Sets the value that should be used to match the index.php file. Defaults
     * to index.php but this allows you to modify it in case you are using
     * something like mod_rewrite to remove the page. This allows you to set
     * it a blank.
     *
     * @param string $page
     *
     * @return RouterInterface
     */
    public function setIndexPage($page);
}
