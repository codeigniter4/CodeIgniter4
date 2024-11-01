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

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class FilterCollectorTest extends CIUnitTestCase
{
    public function testGet(): void
    {
        $routes = service('routes');
        $routes->resetRoutes();
        $routes->setDefaultNamespace('App\Controllers');
        $routes->get('/', 'Home::index');

        $collector = new FilterCollector();

        $filters = $collector->get(Method::GET, '/');

        $expected = [
            'before' => [
            ],
            'after' => [
            ],
        ];
        $this->assertSame($expected, $filters);
    }
}
