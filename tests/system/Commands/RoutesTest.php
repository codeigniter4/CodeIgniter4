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

        $this->registerStreamFilterClass()
            ->appendOutputStreamFilter()
            ->appendErrorStreamFilter();
    }

    protected function tearDown(): void
    {
        $this->removeOutputStreamFilter()->removeErrorStreamFilter();

        $this->resetServices();
    }

    protected function getBuffer()
    {
        return $this->getStreamFilterBuffer();
    }

    public function testRoutesCommand()
    {
        command('routes');

        $this->assertStringContainsString('| (Closure)', $this->getBuffer());
        $this->assertStringContainsString('| Route', $this->getBuffer());
        $this->assertStringContainsString('| testing', $this->getBuffer());
        $this->assertStringContainsString('\\TestController::index', $this->getBuffer());
    }

    public function testRoutesCommandRouteFilterAndAutoRoute()
    {
        $routes = Services::routes();
        $routes->setDefaultNamespace('App\Controllers');
        $routes->resetRoutes();
        $routes->get('/', 'Home::index', ['filter' => 'csrf']);
        $routes->setAutoRoute(true);

        command('routes');

        $this->assertStringContainsString(
            '|auto|/|\App\Controllers\Home::index||toolbar|',
            str_replace(' ', '', $this->getBuffer())
        );
    }
}
