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
use CodeIgniter\Test\Filters\CITestStreamFilter;
use Config\Services;

/**
 * @internal
 */
final class RoutesTest extends CIUnitTestCase
{
    private $streamFilter;

    protected function setUp(): void
    {
        $this->resetServices();

        parent::setUp();

        CITestStreamFilter::$buffer = '';

        $this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->streamFilter);

        $this->resetServices();
    }

    protected function getBuffer()
    {
        return CITestStreamFilter::$buffer;
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
