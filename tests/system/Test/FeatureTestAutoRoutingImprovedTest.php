<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use CodeIgniter\Events\Events;
use Config\Feature;
use Config\Services;

/**
 * @group Others
 *
 * @internal
 */
final class FeatureTestAutoRoutingImprovedTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Events::simulate(true);

        self::initializeRouter();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        Events::simulate(false);

        Services::reset();
    }

    private static function initializeRouter(): void
    {
        $routes = Services::routes();
        $routes->resetRoutes();
        $routes->loadRoutes();

        $routes->setAutoRoute(true);
        config(Feature::class)->autoRoutesImproved = true;

        $namespace = 'Tests\Support\Controllers';
        $routes->setDefaultNamespace($namespace);

        $router = Services::router($routes);

        Services::injectMock('router', $router);
    }

    public function testCallGet(): void
    {
        $response = $this->get('newautorouting');

        $response->assertSee('Hello');
    }

    public function testCallPost(): void
    {
        $response = $this->post('newautorouting/save/1/a/b');

        $response->assertSee('Saved');
    }

    public function testCallParamsCount(): void
    {
        $response = $this->post('newautorouting/save/1/a/b');
        $response->assertSee('Saved');

        $response = $this->get('newautorouting');
        $response->assertSee('Hello');
    }
}
