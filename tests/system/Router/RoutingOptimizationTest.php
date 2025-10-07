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

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Router\Controllers\Mycontroller;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Feature;
use Config\Modules;
use Config\Routing;
use PHPUnit\Framework\Attributes\Group;

/**
 * Integration tests for routing optimization features.
 * Tests the complete flow from config to execution with different routing modes.
 *
 * @internal
 */
#[Group('Others')]
final class RoutingOptimizationTest extends CIUnitTestCase
{
    private IncomingRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $featureConfig                     = config(Feature::class);
        $featureConfig->autoRoutesImproved = true;

        $this->request = service('request');
        $this->request->setMethod(Method::GET);
    }

    private function createRouteCollection(Routing $routingConfig): RouteCollection
    {
        $moduleConfig          = new Modules();
        $moduleConfig->enabled = false;

        $routingConfig->defaultNamespace = 'CodeIgniter\\Router\\Controllers';

        $collection = new RouteCollection(service('locator'), $moduleConfig, $routingConfig);
        $collection->setHTTPVerb(Method::GET);

        return $collection;
    }

    /**
     * Test auto-routing only mode (definedRoutes = false)
     * This should skip all route file loading and discovery
     */
    public function testAutoRoutingOnlyMode(): void
    {
        $routingConfig                = new Routing();
        $routingConfig->autoRoute     = true;
        $routingConfig->definedRoutes = false;

        $collection = $this->createRouteCollection($routingConfig);

        // Add a defined route (should be ignored)
        $collection->get('ignored', 'Ignored::method');

        // Verify no routes are returned
        $this->assertSame([], $collection->getRoutes());

        // Create router and test auto-routing
        $router = new Router($collection, $this->request);
        $router->handle('mycontroller');

        $this->assertSame('\\' . Mycontroller::class, $router->controllerName());
        $this->assertSame('getIndex', $router->methodName());
    }

    /**
     * Test defined routes only mode (autoRoute = false)
     * This should skip AutoRouter instantiation entirely
     */
    public function testDefinedRoutesOnlyMode(): void
    {
        $routingConfig                = new Routing();
        $routingConfig->autoRoute     = false;
        $routingConfig->definedRoutes = true;

        $collection = $this->createRouteCollection($routingConfig);
        $collection->get('products', 'Products::list');

        // Verify route is available
        $routes = $collection->getRoutes();
        $this->assertArrayHasKey('products', $routes);

        // Create router and test defined routing
        $router = new Router($collection, $this->request);
        $router->handle('products');

        $this->assertSame('\CodeIgniter\Router\Controllers\Products', $router->controllerName());
        $this->assertSame('list', $router->methodName());
    }

    /**
     * Test that defined routes only mode throws when no route matches
     * (no fallback to auto-routing)
     */
    public function testDefinedRoutesOnlyModeThrowsOnNoMatch(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage("Can't find a route for 'GET: nonexistent'");

        $routingConfig                = new Routing();
        $routingConfig->autoRoute     = false;
        $routingConfig->definedRoutes = true;

        $collection = $this->createRouteCollection($routingConfig);
        $router     = new Router($collection, $this->request);

        // Should throw immediately without trying auto-routing
        $router->handle('nonexistent');
    }

    /**
     * Test both modes enabled (traditional behavior)
     * Should check defined routes first, then fall back to auto-routing
     */
    public function testBothModesEnabled(): void
    {
        $routingConfig                = new Routing();
        $routingConfig->autoRoute     = true;
        $routingConfig->definedRoutes = true;

        $collection = $this->createRouteCollection($routingConfig);
        $collection->get('users', 'Users::index');

        $router = new Router($collection, $this->request);

        // Test defined route takes precedence
        $router->handle('users');
        $this->assertSame('\CodeIgniter\Router\Controllers\Users', $router->controllerName());
        $this->assertSame('index', $router->methodName());

        // Test fallback to auto-routing
        $router->handle('mycontroller');
        $this->assertSame('\\' . Mycontroller::class, $router->controllerName());
        $this->assertSame('getIndex', $router->methodName());
    }

    /**
     * Test that route file loading is skipped when definedRoutes = false
     */
    public function testRouteFileLoadingSkipped(): void
    {
        $routingConfig                = new Routing();
        $routingConfig->autoRoute     = true;
        $routingConfig->definedRoutes = false;
        $routingConfig->routeFiles    = [APPPATH . 'Config/Routes.php'];

        $collection = $this->createRouteCollection($routingConfig);

        // Call loadRoutes - should return early
        $collection->loadRoutes();

        // Verify routes were not loaded
        $this->assertSame([], $collection->getRoutes());
    }

    /**
     * Test that route discovery is skipped when definedRoutes = false
     */
    public function testRouteDiscoverySkipped(): void
    {
        $routingConfig                = new Routing();
        $routingConfig->autoRoute     = true;
        $routingConfig->definedRoutes = false;

        $moduleConfig          = new Modules();
        $moduleConfig->enabled = true; // Enable discovery

        $collection = new RouteCollection(service('locator'), $moduleConfig, $routingConfig);

        // Verify discovery doesn't happen and routes remain empty
        $this->assertSame([], $collection->getRoutes());
    }

    /**
     * Test configuration flags are properly stored
     */
    public function testConfigurationFlags(): void
    {
        // Test defaults
        $defaultConfig = new Routing();
        $collection    = $this->createRouteCollection($defaultConfig);

        $this->assertFalse($collection->shouldAutoRoute());
        $this->assertTrue($collection->shouldUseDefinedRoutes());

        // Test custom values
        $customConfig                = new Routing();
        $customConfig->autoRoute     = true;
        $customConfig->definedRoutes = false;

        $collection = $this->createRouteCollection($customConfig);

        $this->assertTrue($collection->shouldAutoRoute());
        $this->assertFalse($collection->shouldUseDefinedRoutes());
    }
}
