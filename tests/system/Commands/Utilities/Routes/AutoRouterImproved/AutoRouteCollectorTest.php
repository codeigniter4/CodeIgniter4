<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Utilities\Routes\AutoRouterImproved;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Filters;
use Config\Services;

/**
 * @internal
 */
final class AutoRouteCollectorTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->resetServices(true);
    }

    private function createAutoRouteCollector(array $filterConfigFilters): AutoRouteCollector
    {
        $routes = Services::routes();
        $routes->resetRoutes();
        $routes->setAutoRoute(true);
        config('Feature')->autoRoutesImproved = true;
        $namespace                            = 'Tests\Support\Controllers';
        $routes->setDefaultNamespace($namespace);

        /** @var Filters $filterConfig */
        $filterConfig          = config('Filters');
        $filterConfig->filters = $filterConfigFilters;
        Services::filters($filterConfig);

        return new AutoRouteCollector(
            $namespace,
            'Home',
            'index',
            ['get', 'post'],
            [],
        );
    }

    public function testGetFilterMatches()
    {
        $filterConfigFilters = ['honeypot' => ['before' => ['newautorouting/save*']]];
        $collector           = $this->createAutoRouteCollector($filterConfigFilters);

        $routes = $collector->get();

        $expected = [
            0 => [
                0 => 'GET(auto)',
                1 => 'newautorouting',
                2 => '\\Tests\\Support\\Controllers\\Newautorouting::getIndex',
                3 => '',
                4 => 'toolbar',
            ],
            1 => [
                0 => 'POST(auto)',
                1 => 'newautorouting/save/../..[/..]',
                2 => '\\Tests\\Support\\Controllers\\Newautorouting::postSave',
                3 => 'honeypot',
                4 => 'toolbar',
            ],
        ];
        $this->assertSame($expected, $routes);
    }

    public function testGetFilterDoesNotMatch()
    {
        $filterConfigFilters = ['honeypot' => ['before' => ['newautorouting/save/*/*']]];
        $collector           = $this->createAutoRouteCollector($filterConfigFilters);

        $routes = $collector->get();

        $expected = [
            0 => [
                0 => 'GET(auto)',
                1 => 'newautorouting',
                2 => '\\Tests\\Support\\Controllers\\Newautorouting::getIndex',
                3 => '',
                4 => 'toolbar',
            ],
            1 => [
                0 => 'POST(auto)',
                1 => 'newautorouting/save/../..[/..]',
                2 => '\\Tests\\Support\\Controllers\\Newautorouting::postSave',
                3 => '',
                4 => 'toolbar',
            ],
        ];
        $this->assertSame($expected, $routes);
    }
}
