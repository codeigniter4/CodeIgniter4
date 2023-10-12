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

use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Services;

/**
 * @internal
 *
 * @group Others
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
        return str_replace(PHP_EOL, "\n", $this->getStreamFilterBuffer());
    }

    private function getCleanRoutes(): RouteCollection
    {
        $routes = Services::routes();
        $routes->resetRoutes();
        $routes->loadRoutes();

        return $routes;
    }

    public function testRoutesCommand(): void
    {
        Services::injectMock('routes', null);

        command('routes');

        $expected = <<<'EOL'
            +---------+---------+---------------+----------------------------------------+----------------+---------------+
            | Method  | Route   | Name          | Handler                                | Before Filters | After Filters |
            +---------+---------+---------------+----------------------------------------+----------------+---------------+
            | GET     | /       | »             | \App\Controllers\Home::index           |                | toolbar       |
            | GET     | closure | »             | (Closure)                              |                | toolbar       |
            | GET     | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | HEAD    | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | POST    | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | PUT     | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | DELETE  | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | OPTIONS | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | TRACE   | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | CONNECT | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | CLI     | testing | testing-index | \App\Controllers\TestController::index |                |               |
            +---------+---------+---------------+----------------------------------------+----------------+---------------+
            EOL;
        $this->assertStringContainsString($expected, $this->getBuffer());
    }

    public function testRoutesCommandSortByHandler(): void
    {
        Services::injectMock('routes', null);

        command('routes -h');

        $expected = <<<'EOL'
            +---------+---------+---------------+----------------------------------------+----------------+---------------+
            | Method  | Route   | Name          | Handler ↓                              | Before Filters | After Filters |
            +---------+---------+---------------+----------------------------------------+----------------+---------------+
            | GET     | closure | »             | (Closure)                              |                | toolbar       |
            | GET     | /       | »             | \App\Controllers\Home::index           |                | toolbar       |
            | GET     | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | HEAD    | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | POST    | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | PUT     | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | DELETE  | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | OPTIONS | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | TRACE   | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | CONNECT | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | CLI     | testing | testing-index | \App\Controllers\TestController::index |                |               |
            +---------+---------+---------------+----------------------------------------+----------------+---------------+
            EOL;
        $this->assertStringContainsString($expected, $this->getBuffer());
    }

    public function testRoutesCommandHostHostname()
    {
        Services::injectMock('routes', null);

        command('routes --host blog.example.com');

        $expected = <<<'EOL'
            Host: blog.example.com
            +---------+---------+---------------+----------------------------------------+----------------+---------------+
            | Method  | Route   | Name          | Handler                                | Before Filters | After Filters |
            +---------+---------+---------------+----------------------------------------+----------------+---------------+
            | GET     | /       | »             | \App\Controllers\Blog::index           |                | toolbar       |
            | GET     | closure | »             | (Closure)                              |                | toolbar       |
            | GET     | all     | »             | \App\Controllers\AllDomain::index      |                | toolbar       |
            | GET     | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | HEAD    | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | POST    | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | PUT     | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | DELETE  | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | OPTIONS | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | TRACE   | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | CONNECT | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | CLI     | testing | testing-index | \App\Controllers\TestController::index |                |               |
            +---------+---------+---------------+----------------------------------------+----------------+---------------+
            EOL;
        $this->assertStringContainsString($expected, $this->getBuffer());
    }

    public function testRoutesCommandHostSubdomain()
    {
        Services::injectMock('routes', null);

        command('routes --host sub.example.com');

        $expected = <<<'EOL'
            Host: sub.example.com
            +---------+---------+---------------+----------------------------------------+----------------+---------------+
            | Method  | Route   | Name          | Handler                                | Before Filters | After Filters |
            +---------+---------+---------------+----------------------------------------+----------------+---------------+
            | GET     | /       | »             | \App\Controllers\Sub::index            |                | toolbar       |
            | GET     | closure | »             | (Closure)                              |                | toolbar       |
            | GET     | all     | »             | \App\Controllers\AllDomain::index      |                | toolbar       |
            | GET     | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | HEAD    | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | POST    | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | PUT     | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | DELETE  | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | OPTIONS | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | TRACE   | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | CONNECT | testing | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | CLI     | testing | testing-index | \App\Controllers\TestController::index |                |               |
            +---------+---------+---------------+----------------------------------------+----------------+---------------+
            EOL;
        $this->assertStringContainsString($expected, $this->getBuffer());
    }

    public function testRoutesCommandAutoRouteImproved(): void
    {
        $routes = $this->getCleanRoutes();

        $routes->setAutoRoute(true);
        config('Feature')->autoRoutesImproved = true;
        $namespace                            = 'Tests\Support\Controllers';
        $routes->setDefaultNamespace($namespace);

        command('routes');

        $expected = <<<'EOL'
            +------------+--------------------------------+---------------+-----------------------------------------------------+----------------+---------------+
            | Method     | Route                          | Name          | Handler                                             | Before Filters | After Filters |
            +------------+--------------------------------+---------------+-----------------------------------------------------+----------------+---------------+
            | GET        | /                              | »             | \App\Controllers\Home::index                        |                | toolbar       |
            | GET        | closure                        | »             | (Closure)                                           |                | toolbar       |
            | GET        | testing                        | testing-index | \App\Controllers\TestController::index              |                | toolbar       |
            | HEAD       | testing                        | testing-index | \App\Controllers\TestController::index              |                | toolbar       |
            | POST       | testing                        | testing-index | \App\Controllers\TestController::index              |                | toolbar       |
            | PUT        | testing                        | testing-index | \App\Controllers\TestController::index              |                | toolbar       |
            | DELETE     | testing                        | testing-index | \App\Controllers\TestController::index              |                | toolbar       |
            | OPTIONS    | testing                        | testing-index | \App\Controllers\TestController::index              |                | toolbar       |
            | TRACE      | testing                        | testing-index | \App\Controllers\TestController::index              |                | toolbar       |
            | CONNECT    | testing                        | testing-index | \App\Controllers\TestController::index              |                | toolbar       |
            | CLI        | testing                        | testing-index | \App\Controllers\TestController::index              |                |               |
            | GET(auto)  | newautorouting[/..]            |               | \Tests\Support\Controllers\Newautorouting::getIndex |                | toolbar       |
            | POST(auto) | newautorouting/save/../..[/..] |               | \Tests\Support\Controllers\Newautorouting::postSave |                | toolbar       |
            +------------+--------------------------------+---------------+-----------------------------------------------------+----------------+---------------+
            EOL;
        $this->assertStringContainsString($expected, $this->getBuffer());
    }

    public function testRoutesCommandRouteLegacy(): void
    {
        $routes = $this->getCleanRoutes();
        $routes->loadRoutes();

        $routes->setAutoRoute(true);

        command('routes');

        $expected = <<<'EOL'
            +---------+------------------+---------------+----------------------------------------+----------------+---------------+
            | Method  | Route            | Name          | Handler                                | Before Filters | After Filters |
            +---------+------------------+---------------+----------------------------------------+----------------+---------------+
            | GET     | /                | »             | \App\Controllers\Home::index           |                | toolbar       |
            | GET     | closure          | »             | (Closure)                              |                | toolbar       |
            | GET     | testing          | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | HEAD    | testing          | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | POST    | testing          | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | PUT     | testing          | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | DELETE  | testing          | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | OPTIONS | testing          | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | TRACE   | testing          | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | CONNECT | testing          | testing-index | \App\Controllers\TestController::index |                | toolbar       |
            | CLI     | testing          | testing-index | \App\Controllers\TestController::index |                |               |
            | auto    | /                |               | \App\Controllers\Home::index           |                | toolbar       |
            | auto    | home             |               | \App\Controllers\Home::index           |                | toolbar       |
            | auto    | home/index[/...] |               | \App\Controllers\Home::index           |                | toolbar       |
            +---------+------------------+---------------+----------------------------------------+----------------+---------------+
            EOL;
        $this->assertStringContainsString($expected, $this->getBuffer());
    }
}
