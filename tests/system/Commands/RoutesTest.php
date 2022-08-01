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

    private function getCleanRoutes(): RouteCollection
    {
        $routes = Services::routes();
        $routes->resetRoutes();
        $routes->loadRoutes();

        return $routes;
    }

    public function testRoutesCommand()
    {
        $this->getCleanRoutes();

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

    public function testRoutesCommandAutoRouteImproved()
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
            | GET(auto)  | newautorouting                 |               | \Tests\Support\Controllers\Newautorouting::getIndex |                | toolbar       |
            | POST(auto) | newautorouting/save/../..[/..] |               | \Tests\Support\Controllers\Newautorouting::postSave |                | toolbar       |
            +------------+--------------------------------+---------------+-----------------------------------------------------+----------------+---------------+
            EOL;
        $this->assertStringContainsString($expected, $this->getBuffer());
    }

    public function testRoutesCommandRouteLegacy()
    {
        $routes = $this->getCleanRoutes();

        $routes->setAutoRoute(true);
        $namespace = 'Tests\Support\Controllers';
        $routes->setDefaultNamespace($namespace);

        command('routes');

        $expected = <<<'EOL'
            +---------+-------------------------------+---------------+-----------------------------------------------------+----------------+---------------+
            | Method  | Route                         | Name          | Handler                                             | Before Filters | After Filters |
            +---------+-------------------------------+---------------+-----------------------------------------------------+----------------+---------------+
            | GET     | /                             | »             | \App\Controllers\Home::index                        |                | toolbar       |
            | GET     | closure                       | »             | (Closure)                                           |                | toolbar       |
            | GET     | testing                       | testing-index | \App\Controllers\TestController::index              |                | toolbar       |
            | HEAD    | testing                       | testing-index | \App\Controllers\TestController::index              |                | toolbar       |
            | POST    | testing                       | testing-index | \App\Controllers\TestController::index              |                | toolbar       |
            | PUT     | testing                       | testing-index | \App\Controllers\TestController::index              |                | toolbar       |
            | DELETE  | testing                       | testing-index | \App\Controllers\TestController::index              |                | toolbar       |
            | OPTIONS | testing                       | testing-index | \App\Controllers\TestController::index              |                | toolbar       |
            | TRACE   | testing                       | testing-index | \App\Controllers\TestController::index              |                | toolbar       |
            | CONNECT | testing                       | testing-index | \App\Controllers\TestController::index              |                | toolbar       |
            | CLI     | testing                       | testing-index | \App\Controllers\TestController::index              |                |               |
            | auto    | hello                         |               | \Tests\Support\Controllers\Hello::index             |                | toolbar       |
            | auto    | hello/index[/...]             |               | \Tests\Support\Controllers\Hello::index             |                | toolbar       |
            | auto    | newautorouting/getIndex[/...] |               | \Tests\Support\Controllers\Newautorouting::getIndex |                | toolbar       |
            | auto    | newautorouting/postSave[/...] |               | \Tests\Support\Controllers\Newautorouting::postSave |                | toolbar       |
            | auto    | popcorn                       |               | \Tests\Support\Controllers\Popcorn::index           |                | toolbar       |
            | auto    | popcorn/index[/...]           |               | \Tests\Support\Controllers\Popcorn::index           |                | toolbar       |
            | auto    | popcorn/pop[/...]             |               | \Tests\Support\Controllers\Popcorn::pop             |                | toolbar       |
            | auto    | popcorn/popper[/...]          |               | \Tests\Support\Controllers\Popcorn::popper          |                | toolbar       |
            | auto    | popcorn/weasel[/...]          |               | \Tests\Support\Controllers\Popcorn::weasel          |                | toolbar       |
            | auto    | popcorn/oops[/...]            |               | \Tests\Support\Controllers\Popcorn::oops            |                | toolbar       |
            | auto    | popcorn/goaway[/...]          |               | \Tests\Support\Controllers\Popcorn::goaway          |                | toolbar       |
            | auto    | popcorn/index3[/...]          |               | \Tests\Support\Controllers\Popcorn::index3          |                | toolbar       |
            | auto    | popcorn/canyon[/...]          |               | \Tests\Support\Controllers\Popcorn::canyon          |                | toolbar       |
            | auto    | popcorn/cat[/...]             |               | \Tests\Support\Controllers\Popcorn::cat             |                | toolbar       |
            | auto    | popcorn/json[/...]            |               | \Tests\Support\Controllers\Popcorn::json            |                | toolbar       |
            | auto    | popcorn/xml[/...]             |               | \Tests\Support\Controllers\Popcorn::xml             |                | toolbar       |
            | auto    | popcorn/toindex[/...]         |               | \Tests\Support\Controllers\Popcorn::toindex         |                | toolbar       |
            | auto    | popcorn/echoJson[/...]        |               | \Tests\Support\Controllers\Popcorn::echoJson        |                | toolbar       |
            | auto    | remap[/...]                   |               | \Tests\Support\Controllers\Remap::_remap            |                | toolbar       |
            +---------+-------------------------------+---------------+-----------------------------------------------------+----------------+---------------+
            EOL;
        $this->assertStringContainsString($expected, $this->getBuffer());
    }
}
