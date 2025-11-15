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

use Closure;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Interface RouteCollectionInterface
 *
 * A Route Collection's sole job is to hold a series of routes. The required
 * number of methods is kept very small on purpose, but implementors may
 * add a number of additional methods to customize how the routes are defined.
 */
interface RouteCollectionInterface
{
    /**
     * Adds a single route to the collection.
     *
     * @param string                                                            $from    The route path (with placeholders or regex)
     * @param array|(Closure(mixed...): (ResponseInterface|string|void))|string $to      The route handler
     * @param array|null                                                        $options The route options
     *
     * @return RouteCollectionInterface
     */
    public function add(string $from, $to, ?array $options = null);

    /**
     * Registers a new constraint with the system. Constraints are used
     * by the routes as placeholders for regular expressions to make defining
     * the routes more human-friendly.
     *
     * You can pass an associative array as $placeholder, and have
     * multiple placeholders added at once.
     *
     * @param array|string $placeholder
     * @param string|null  $pattern     The regex pattern
     *
     * @return RouteCollectionInterface
     */
    public function addPlaceholder($placeholder, ?string $pattern = null);

    /**
     * Sets the default namespace to use for Controllers when no other
     * namespace has been specified.
     *
     * @return RouteCollectionInterface
     */
    public function setDefaultNamespace(string $value);

    /**
     * Returns the default namespace.
     */
    public function getDefaultNamespace(): string;

    /**
     * Sets the default controller to use when no other controller has been
     * specified.
     *
     * @return RouteCollectionInterface
     *
     * @TODO The default controller is only for auto-routing. So this should be
     *      removed in the future.
     */
    public function setDefaultController(string $value);

    /**
     * Sets the default method to call on the controller when no other
     * method has been set in the route.
     *
     * @return RouteCollectionInterface
     */
    public function setDefaultMethod(string $value);

    /**
     * Tells the system whether to convert dashes in URI strings into
     * underscores. In some search engines, including Google, dashes
     * create more meaning and make it easier for the search engine to
     * find words and meaning in the URI for better SEO. But it
     * doesn't work well with PHP method names....
     *
     * @return RouteCollectionInterface
     *
     * @TODO This method is only for auto-routing. So this should be removed in
     *      the future.
     */
    public function setTranslateURIDashes(bool $value);

    /**
     * If TRUE, the system will attempt to match the URI against
     * Controllers by matching each segment against folders/files
     * in APPPATH/Controllers, when a match wasn't found against
     * defined routes.
     *
     * If FALSE, will stop searching and do NO automatic routing.
     *
     * @TODO This method is only for auto-routing. So this should be removed in
     *      the future.
     */
    public function setAutoRoute(bool $value): self;

    /**
     * Sets the class/method that should be called if routing doesn't
     * find a match. It can be either a closure or the controller/method
     * name exactly like a route is defined: Users::index
     *
     * This setting is passed to the Router class and handled there.
     *
     * @param callable|null $callable
     *
     * @TODO This method is not related to the route collection. So this should
     *      be removed in the future.
     */
    public function set404Override($callable = null): self;

    /**
     * Returns the 404 Override setting, which can be null, a closure
     * or the controller/string.
     *
     * @return (Closure(string): (ResponseInterface|string|void))|string|null
     *
     * @TODO This method is not related to the route collection. So this should
     *      be removed in the future.
     */
    public function get404Override();

    /**
     * Returns the name of the default controller. With Namespace.
     *
     * @return string
     *
     * @TODO The default controller is only for auto-routing. So this should be
     *      removed in the future.
     */
    public function getDefaultController();

    /**
     * Returns the name of the default method to use within the controller.
     *
     * @return string
     */
    public function getDefaultMethod();

    /**
     * Returns the current value of the translateURIDashes setting.
     *
     * @return bool
     *
     * @TODO This method is only for auto-routing. So this should be removed in
     *      the future.
     */
    public function shouldTranslateURIDashes();

    /**
     * Returns the flag that tells whether to autoRoute URI against Controllers.
     *
     * @return bool
     *
     * @TODO This method is only for auto-routing. So this should be removed in
     *      the future.
     */
    public function shouldAutoRoute();

    /**
     * Returns the raw array of available routes.
     *
     * @param non-empty-string|null $verb            HTTP verb like `GET`,`POST` or `*` or `CLI`.
     * @param bool                  $includeWildcard Whether to include '*' routes.
     */
    public function getRoutes(?string $verb = null, bool $includeWildcard = true): array;

    /**
     * Returns one or all routes options
     *
     * @param string|null $from The route path (with placeholders or regex)
     * @param string|null $verb HTTP verb like `GET`,`POST` or `*` or `CLI`.
     *
     * @return array<string, int|string> [key => value]
     */
    public function getRoutesOptions(?string $from = null, ?string $verb = null): array;

    /**
     * Sets the current HTTP verb.
     *
     * @param string $verb HTTP verb
     *
     * @return $this
     */
    public function setHTTPVerb(string $verb);

    /**
     * Returns the current HTTP Verb being used.
     *
     * @return string
     */
    public function getHTTPVerb();

    /**
     * Attempts to look up a route based on its destination.
     *
     * If a route exists:
     *
     *      'path/(:any)/(:any)' => 'Controller::method/$1/$2'
     *
     * This method allows you to know the Controller and method
     * and get the route that leads to it.
     *
     *      // Equals 'path/$param1/$param2'
     *      reverseRoute('Controller::method', $param1, $param2);
     *
     * @param string     $search    Named route or Controller::method
     * @param int|string ...$params
     *
     * @return false|string The route (URI path relative to baseURL) or false if not found.
     */
    public function reverseRoute(string $search, ...$params);

    /**
     * Determines if the route is a redirecting route.
     */
    public function isRedirect(string $routeKey): bool;

    /**
     * Grabs the HTTP status code from a redirecting Route.
     */
    public function getRedirectCode(string $routeKey): int;

    /**
     * Get the flag that limit or not the routes with {locale} placeholder to App::$supportedLocales
     */
    public function shouldUseSupportedLocalesOnly(): bool;

    /**
     * Checks a route (using the "from") to see if it's filtered or not.
     *
     * @param string|null $verb HTTP verb like `GET`,`POST` or `*` or `CLI`.
     */
    public function isFiltered(string $search, ?string $verb = null): bool;

    /**
     * Returns the filters that should be applied for a single route, along
     * with any parameters it might have. Parameters are found by splitting
     * the parameter name on a colon to separate the filter name from the parameter list,
     * and the splitting the result on commas. So:
     *
     *    'role:admin,manager'
     *
     * has a filter of "role", with parameters of ['admin', 'manager'].
     *
     * @param string      $search routeKey
     * @param string|null $verb   HTTP verb like `GET`,`POST` or `*` or `CLI`.
     *
     * @return list<string> filter_name or filter_name:arguments like 'role:admin,manager'
     */
    public function getFiltersForRoute(string $search, ?string $verb = null): array;
}
