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

namespace CodeIgniter\Commands\Utilities\Routes;

use CodeIgniter\Config\Services;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Filters;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Router\Router;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ConfigFromArrayTrait;
use Config\Feature;
use Config\Filters as FiltersConfig;
use Config\Modules;
use Config\Routing;

/**
 * @internal
 *
 * @group Others
 */
final class FilterFinderTest extends CIUnitTestCase
{
    use ConfigFromArrayTrait;

    private IncomingRequest $request;
    private Response $response;
    private Modules $moduleConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request  = Services::request();
        $this->response = Services::response();

        $this->moduleConfig          = new Modules();
        $this->moduleConfig->enabled = false;
    }

    private function createRouteCollection(array $routes = []): RouteCollection
    {
        $collection = new RouteCollection(Services::locator(), $this->moduleConfig, new Routing());

        $routes = ($routes !== []) ? $routes : [
            'users'                   => 'Users::index',
            'user-setting/show-list'  => 'User_setting::show_list',
            'user-setting/(:segment)' => 'User_setting::detail/$1',
        ];

        return $collection->map($routes);
    }

    private function createRouter(RouteCollection $collection): Router
    {
        return new Router($collection, $this->request);
    }

    private function createFilters(array $config = []): Filters
    {
        $config = ($config !== []) ? $config : [
            'aliases' => [
                'csrf'     => CSRF::class,
                'toolbar'  => DebugToolbar::class,
                'honeypot' => Honeypot::class,
            ],
            'globals' => [
                'before' => [
                    'csrf',
                ],
                'after' => [
                    'toolbar',
                ],
            ],
            'methods' => [
                'GET' => [],
            ],
            'filters' => [
                'honeypot' => ['before' => ['form/*', 'survey/*']],
            ],
        ];
        $filtersConfig = $this->createConfigFromArray(FiltersConfig::class, $config);

        return new Filters($filtersConfig, $this->request, $this->response, $this->moduleConfig);
    }

    public function testFindGlobalsFilters(): void
    {
        $collection = $this->createRouteCollection();
        $router     = $this->createRouter($collection);
        $filters    = $this->createFilters();

        $finder = new FilterFinder($router, $filters);

        $filters = $finder->find('users');

        $expected = [
            'before' => ['csrf'],
            'after'  => ['toolbar'],
        ];
        $this->assertSame($expected, $filters);
    }

    public function testFindGlobalsFiltersWithRedirectRoute(): void
    {
        $collection = $this->createRouteCollection();
        $collection->addRedirect('users/about', 'profile');

        $router  = $this->createRouter($collection);
        $filters = $this->createFilters();

        $finder = new FilterFinder($router, $filters);

        $filters = $finder->find('users/about');

        $expected = [
            'before' => [],
            'after'  => [],
        ];
        $this->assertSame($expected, $filters);
    }

    public function testFindGlobalsAndRouteFilters(): void
    {
        $collection = $this->createRouteCollection();
        $collection->get('admin', ' AdminController::index', ['filter' => 'honeypot']);
        $router  = $this->createRouter($collection);
        $filters = $this->createFilters();

        $finder = new FilterFinder($router, $filters);

        $filters = $finder->find('admin');

        $expected = [
            'before' => ['csrf', 'honeypot'],
            'after'  => ['honeypot', 'toolbar'],
        ];
        $this->assertSame($expected, $filters);
    }

    public function testFindGlobalsAndRouteClassnameFilters(): void
    {
        $collection = $this->createRouteCollection();
        $collection->get('admin', ' AdminController::index', ['filter' => InvalidChars::class]);
        $router  = $this->createRouter($collection);
        $filters = $this->createFilters();

        $finder = new FilterFinder($router, $filters);

        $filters = $finder->find('admin');

        $expected = [
            'before' => ['csrf', InvalidChars::class],
            'after'  => [InvalidChars::class, 'toolbar'],
        ];
        $this->assertSame($expected, $filters);
    }

    public function testFindGlobalsAndRouteMultipleFilters(): void
    {
        $collection = $this->createRouteCollection();
        $collection->get('admin', ' AdminController::index', ['filter' => ['honeypot', InvalidChars::class]]);
        $router  = $this->createRouter($collection);
        $filters = $this->createFilters();

        $finder = new FilterFinder($router, $filters);

        $filters = $finder->find('admin');

        $expected = [
            'before' => ['csrf', 'honeypot', InvalidChars::class],
            'after'  => [InvalidChars::class, 'honeypot', 'toolbar'],
        ];
        $this->assertSame($expected, $filters);
    }

    public function testFilterOrder(): void
    {
        $collection = $this->createRouteCollection([]);
        $collection->get('/', ' Home::index', ['filter' => ['route1', 'route2']]);
        $router  = $this->createRouter($collection);
        $filters = $this->createFilters([
            'aliases' => [
                'global1' => 'Dummy',
                'global2' => 'Dummy',
                'method1' => 'Dummy',
                'method2' => 'Dummy',
                'filter1' => 'Dummy',
                'filter2' => 'Dummy',
                'route1'  => 'Dummy',
                'route2'  => 'Dummy',
            ],
            'globals' => [
                'before' => [
                    'global1',
                    'global2',
                ],
                'after' => [
                    'global2',
                    'global1',
                ],
            ],
            'methods' => [
                'GET' => ['method1', 'method2'],
            ],
            'filters' => [
                'filter1' => ['before' => '*', 'after' => '*'],
                'filter2' => ['before' => '*', 'after' => '*'],
            ],
        ]);

        $finder = new FilterFinder($router, $filters);

        $filters = $finder->find('/');

        $expected = [
            'before' => [
                'global1',
                'global2',
                'method1',
                'method2',
                'filter1',
                'filter2',
                'route1',
                'route2',
            ],
            'after' => [
                'route2',
                'route1',
                'filter2',
                'filter1',
                'global2',
                'global1',
            ],
        ];
        $this->assertSame($expected, $filters);
    }

    public function testFilterOrderWithOldFilterOrder(): void
    {
        $feature                 = config(Feature::class);
        $feature->oldFilterOrder = true;

        $collection = $this->createRouteCollection([]);
        $collection->get('/', ' Home::index', ['filter' => ['route1', 'route2']]);
        $router  = $this->createRouter($collection);
        $filters = $this->createFilters([
            'aliases' => [
                'global1' => 'Dummy',
                'global2' => 'Dummy',
                'method1' => 'Dummy',
                'method2' => 'Dummy',
                'filter1' => 'Dummy',
                'filter2' => 'Dummy',
                'route1'  => 'Dummy',
                'route2'  => 'Dummy',
            ],
            'globals' => [
                'before' => [
                    'global1',
                    'global2',
                ],
                'after' => [
                    'global1',
                    'global2',
                ],
            ],
            'methods' => [
                'GET' => ['method1', 'method2'],
            ],
            'filters' => [
                'filter1' => ['before' => '*', 'after' => '*'],
                'filter2' => ['before' => '*', 'after' => '*'],
            ],
        ]);

        $finder = new FilterFinder($router, $filters);

        $filters = $finder->find('/');

        $expected = [
            'before' => [
                'route1',
                'route2',
                'global1',
                'global2',
                'method1',
                'method2',
                'filter1',
                'filter2',
            ],
            'after' => [
                'route1',
                'route2',
                'global1',
                'global2',
                'filter1',
                'filter2',
            ],
        ];
        $this->assertSame($expected, $filters);
    }
}
