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

use CodeIgniter\HTTP\Request;

/**
 * Expected behavior of a Router.
 */
interface RouterInterface
{
    /**
     * Stores a reference to the RouteCollection object.
     *
     * @param Request $request
     */
    public function __construct(RouteCollectionInterface $routes, ?Request $request = null);

    /**
     * Scans the URI and attempts to match the current URI to the
     * one of the defined routes in the RouteCollection.
     *
     * @param string $uri
     *
     * @return mixed
     */
    public function handle(?string $uri = null);

    /**
     * Returns the name of the matched controller.
     *
     * @return mixed
     */
    public function controllerName();

    /**
     * Returns the name of the method to run in the
     * chosen container.
     *
     * @return mixed
     */
    public function methodName();

    /**
     * Returns the binds that have been matched and collected
     * during the parsing process as an array, ready to send to
     * instance->method(...$params).
     *
     * @return mixed
     */
    public function params();

    /**
     * Sets the value that should be used to match the index.php file. Defaults
     * to index.php but this allows you to modify it in case your are using
     * something like mod_rewrite to remove the page. This allows you to set
     * it a blank.
     *
     * @param string $page
     *
     * @return mixed
     */
    public function setIndexPage($page);
}
