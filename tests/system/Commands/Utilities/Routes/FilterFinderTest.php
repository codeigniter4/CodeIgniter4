<?php

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
                'get' => [],
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
            'before' => ['honeypot', 'csrf'],
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
            'before' => [InvalidChars::class, 'csrf'],
            'after'  => [InvalidChars::class, 'toolbar'],
        ];
        $this->assertSame($expected, $filters);
    }

    public function testFindGlobalsAndRouteMultipleFilters(): void
    {
        config('Feature')->multipleFilters = true;

        $collection = $this->createRouteCollection();
        $collection->get('admin', ' AdminController::index', ['filter' => ['honeypot', InvalidChars::class]]);
        $router  = $this->createRouter($collection);
        $filters = $this->createFilters();

        $finder = new FilterFinder($router, $filters);

        $filters = $finder->find('admin');

        $expected = [
            'before' => ['honeypot', InvalidChars::class, 'csrf'],
            'after'  => ['honeypot', InvalidChars::class, 'toolbar'],
        ];
        $this->assertSame($expected, $filters);

        config('Feature')->multipleFilters = false;
    }
}
