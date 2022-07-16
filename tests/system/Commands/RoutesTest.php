<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Services;

/**
 * @internal
 */
final class RoutesTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        $this->resetServices();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->resetServices();
        parent::tearDown();
    }

    protected function getBuffer()
    {
        return $this->getStreamFilterBuffer();
    }

    private function getBufferWithoutSpaces(): string
    {
        return str_replace(' ', '', $this->getBuffer());
    }

    public function testRoutesCommand()
    {
        command('routes');

        $this->assertStringContainsString('| (Closure)', $this->getBuffer());
        $this->assertStringContainsString('| Route', $this->getBuffer());
        $this->assertStringContainsString('| testing', $this->getBuffer());
        $this->assertStringContainsString('\\TestController::index', $this->getBuffer());
    }

    public function testRoutesCommandRouteFilterAndAutoRouteLegacy()
    {
        $routes = Services::routes();
        $routes->setDefaultNamespace('App\Controllers');
        $routes->resetRoutes();
        $routes->get('/', 'Home::index', ['filter' => 'csrf']);
        $routes->setAutoRoute(true);

        command('routes');

        $this->assertStringContainsString(
            '|auto|/||\App\Controllers\Home::index||toolbar|',
            $this->getBufferWithoutSpaces()
        );
    }

    public function testRoutesCommandRouteFilterAndAutoRouteImproved()
    {
        $routes = Services::routes();
        $routes->resetRoutes();
        $routes->loadRoutes();
        $routes->setAutoRoute(true);
        config('Feature')->autoRoutesImproved = true;
        $namespace                            = 'Tests\Support\Controllers';
        $routes->setDefaultNamespace($namespace);

        command('routes');

        $this->assertStringContainsString(
            '|GET|/|»|\App\Controllers\Home::index||toolbar|',
            $this->getBufferWithoutSpaces()
        );
        $this->assertStringContainsString(
            '|GET|closure|»|(Closure)||toolbar|',
            $this->getBufferWithoutSpaces()
        );
        $this->assertStringContainsString(
            '|GET|testing|testing-index|\App\Controllers\TestController::index||toolbar|',
            $this->getBufferWithoutSpaces()
        );
        $this->assertStringContainsString(
            '|GET(auto)|newautorouting||\Tests\Support\Controllers\Newautorouting::getIndex||toolbar|',
            $this->getBufferWithoutSpaces()
        );
        $this->assertStringContainsString(
            '|POST(auto)|newautorouting/save/../..[/..]||\Tests\Support\Controllers\Newautorouting::postSave||toolbar|',
            $this->getBufferWithoutSpaces()
        );
    }
}
