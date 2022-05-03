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
use Tests\Support\Controllers\Newautorouting;
use Tests\Support\Controllers\Remap;

/**
 * @internal
 */
final class ControllerMethodReaderTest extends CIUnitTestCase
{
    private function createControllerMethodReader(): ControllerMethodReader
    {
        $methods = [
            'get',
            'post',
        ];
        $namespace = 'Tests\Support\Controllers';

        return new ControllerMethodReader($namespace, $methods);
    }

    public function testRead()
    {
        $reader = $this->createControllerMethodReader();

        $routes = $reader->read(Newautorouting::class);

        $expected = [
            0 => [
                'method'       => 'get',
                'route'        => 'newautorouting',
                'route_params' => '',
                'handler'      => '\Tests\Support\Controllers\Newautorouting::getIndex',
                'params'       => [],
            ],
            [
                'method'       => 'post',
                'route'        => 'newautorouting/save',
                'route_params' => '/../..[/..]',
                'handler'      => '\Tests\Support\Controllers\Newautorouting::postSave',
                'params'       => [
                    'a' => true,
                    'b' => true,
                    'c' => false,
                ],
            ],
        ];

        $this->assertSame($expected, $routes);
    }

    public function testReadControllerWithRemap()
    {
        $reader = $this->createControllerMethodReader();

        $routes = $reader->read(Remap::class);

        $expected = [];
        $this->assertSame($expected, $routes);
    }
}
