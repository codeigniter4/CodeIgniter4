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

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class FilterCollectorTest extends CIUnitTestCase
{
    public function testGet()
    {
        $routes = Services::routes();
        $routes->resetRoutes();

        $collector = new FilterCollector();

        $filters = $collector->get('get', '/');

        $expected = [
            'before' => [
            ],
            'after' => [
                'toolbar',
            ],
        ];
        $this->assertSame($expected, $filters);
    }
}
