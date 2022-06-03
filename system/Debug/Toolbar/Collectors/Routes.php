<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Debug\Toolbar\Collectors;

use Config\Services;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Routes collector
 */
class Routes extends BaseCollector
{
    /**
     * Whether this collector has data that can
     * be displayed in the Timeline.
     *
     * @var bool
     */
    protected $hasTimeline = false;

    /**
     * Whether this collector needs to display
     * content in a tab or not.
     *
     * @var bool
     */
    protected $hasTabContent = true;

    /**
     * The 'title' of this Collector.
     * Used to name things in the toolbar HTML.
     *
     * @var string
     */
    protected $title = 'Routes';

    /**
     * Returns the data of this collector to be formatted in the toolbar
     *
     * @throws ReflectionException
     */
    public function display(): array
    {
        $rawRoutes = Services::routes(true);
        $router    = Services::router(null, null, true);

        // Matched Route
        $route = $router->getMatchedRoute();

        // Get our parameters
        // Closure routes
        if (is_callable($router->controllerName())) {
            $method = new ReflectionFunction($router->controllerName());
        } else {
            try {
                $method = new ReflectionMethod($router->controllerName(), $router->methodName());
            } catch (ReflectionException $e) {
                // If we're here, the method doesn't exist
                // and is likely calculated in _remap.
                $method = new ReflectionMethod($router->controllerName(), '_remap');
            }
        }

        $rawParams = $method->getParameters();

        $params = [];

        foreach ($rawParams as $key => $param) {
            $params[] = [
                'name'  => '$' . $param->getName() . ' = ',
                'value' => $router->params()[$key] ??
                    ' <empty> | default: '
                    . var_export(
                        $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
                        true
                    ),
            ];
        }

        $matchedRoute = [
            [
                'directory'  => $router->directory(),
                'controller' => $router->controllerName(),
                'method'     => $router->methodName(),
                'paramCount' => count($router->params()),
                'truePCount' => count($params),
                'params'     => $params,
            ],
        ];

        // Defined Routes
        $routes  = [];
        $methods = [
            'get',
            'head',
            'post',
            'patch',
            'put',
            'delete',
            'options',
            'trace',
            'connect',
            'cli',
        ];

        foreach ($methods as $method) {
            $raw = $rawRoutes->getRoutes($method);

            foreach ($raw as $route => $handler) {
                // filter for strings, as callbacks aren't displayable
                if (is_string($handler)) {
                    $routes[] = [
                        'method'  => strtoupper($method),
                        'route'   => $route,
                        'handler' => $handler,
                    ];
                }
            }
        }

        return [
            'matchedRoute' => $matchedRoute,
            'routes'       => $routes,
        ];
    }

    /**
     * Returns a count of all the routes in the system.
     */
    public function getBadgeValue(): int
    {
        $rawRoutes = Services::routes(true);

        return count($rawRoutes->getRoutes());
    }

    /**
     * Display the icon.
     *
     * Icon from https://icons8.com - 1em package
     */
    public function icon(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAFDSURBVEhL7ZRNSsNQFIUjVXSiOFEcuQIHDpzpxC0IGYeE/BEInbWlCHEDLsSiuANdhKDjgm6ggtSJ+l25ldrmmTwIgtgDh/t37r1J+16cX0dRFMtpmu5pWAkrvYjjOB7AETzStBFW+inxu3KUJMmhludQpoflS1zXban4LYqiO224h6VLTHr8Z+z8EpIHFF9gG78nDVmW7UgTHKjsCyY98QP+pcq+g8Ku2s8G8X3f3/I8b038WZTp+bO38zxfFd+I6YY6sNUvFlSDk9CRhiAI1jX1I9Cfw7GG1UB8LAuwbU0ZwQnbRDeEN5qqBxZMLtE1ti9LtbREnMIuOXnyIf5rGIb7Wq8HmlZgwYBH7ORTcKH5E4mpjeGt9fBZcHE2GCQ3Vt7oTNPNg+FXLHnSsHkw/FR+Gg2bB8Ptzrst/v6C/wrH+QB+duli6MYJdQAAAABJRU5ErkJggg==';
    }
}
