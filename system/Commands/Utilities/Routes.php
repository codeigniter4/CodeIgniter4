<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Utilities;

use Closure;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Commands\Utilities\Routes\AutoRouteCollector;
use CodeIgniter\Commands\Utilities\Routes\FilterCollector;
use CodeIgniter\Commands\Utilities\Routes\SampleURIGenerator;
use Config\Services;

/**
 * Lists all the routes. This will include any Routes files
 * that can be discovered, and will include routes that are not defined
 * in routes files, but are instead discovered through auto-routing.
 */
class Routes extends BaseCommand
{
    /**
     * The group the command is lumped under
     * when listing commands.
     *
     * @var string
     */
    protected $group = 'CodeIgniter';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'routes';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Displays all routes.';

    /**
     * the Command's usage
     *
     * @var string
     */
    protected $usage = 'routes';

    /**
     * the Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * the Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Displays the help for the spark cli script itself.
     */
    public function run(array $params)
    {
        $collection = Services::routes(true);
        $methods    = [
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

        $tbody           = [];
        $uriGenerator    = new SampleURIGenerator();
        $filterCollector = new FilterCollector();

        foreach ($methods as $method) {
            $routes = $collection->getRoutes($method);

            foreach ($routes as $route => $handler) {
                if (is_string($handler) || $handler instanceof Closure) {
                    $sampleUri = $uriGenerator->get($route);
                    $filters   = $filterCollector->get($method, $sampleUri);

                    $tbody[] = [
                        strtoupper($method),
                        $route,
                        is_string($handler) ? $handler : '(Closure)',
                        implode(' ', array_map('class_basename', $filters['before'])),
                        implode(' ', array_map('class_basename', $filters['after'])),
                    ];
                }
            }
        }

        if ($collection->shouldAutoRoute()) {
            $autoRouteCollector = new AutoRouteCollector(
                $collection->getDefaultNamespace(),
                $collection->getDefaultController(),
                $collection->getDefaultMethod()
            );
            $autoRoutes = $autoRouteCollector->get();

            foreach ($autoRoutes as &$routes) {
                // There is no `auto` method, but it is intentional not to get route filters.
                $filters = $filterCollector->get('auto', $uriGenerator->get($routes[1]));

                $routes[] = implode(' ', array_map('class_basename', $filters['before']));
                $routes[] = implode(' ', array_map('class_basename', $filters['after']));
            }

            $tbody = [...$tbody, ...$autoRoutes];
        }

        $thead = [
            'Method',
            'Route',
            'Handler',
            'Before Filters',
            'After Filters',
        ];

        CLI::table($tbody, $thead);
    }
}
