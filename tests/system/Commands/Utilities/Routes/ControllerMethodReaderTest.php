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
use Tests\Support\Controllers\Popcorn;
use Tests\Support\Controllers\Remap;

/**
 * @internal
 *
 * @group Others
 */
final class ControllerMethodReaderTest extends CIUnitTestCase
{
    public function testRead(): void
    {
        $namespace = 'Tests\Support\Controllers';
        $reader    = new ControllerMethodReader($namespace);

        $routes = $reader->read(Popcorn::class);

        $expected = [
            0 => [
                'route'   => 'popcorn',
                'handler' => '\\Tests\\Support\\Controllers\\Popcorn::index',
            ],
            1 => [
                'route'   => 'popcorn/index[/...]',
                'handler' => '\\Tests\\Support\\Controllers\\Popcorn::index',
            ],
            2 => [
                'route'   => 'popcorn/pop[/...]',
                'handler' => '\\Tests\\Support\\Controllers\\Popcorn::pop',
            ],
            3 => [
                'route'   => 'popcorn/popper[/...]',
                'handler' => '\\Tests\\Support\\Controllers\\Popcorn::popper',
            ],
            4 => [
                'route'   => 'popcorn/weasel[/...]',
                'handler' => '\\Tests\\Support\\Controllers\\Popcorn::weasel',
            ],
            5 => [
                'route'   => 'popcorn/oops[/...]',
                'handler' => '\\Tests\\Support\\Controllers\\Popcorn::oops',
            ],
            6 => [
                'route'   => 'popcorn/goaway[/...]',
                'handler' => '\\Tests\\Support\\Controllers\\Popcorn::goaway',
            ],
            7 => [
                'route'   => 'popcorn/index3[/...]',
                'handler' => '\\Tests\\Support\\Controllers\\Popcorn::index3',
            ],
            8 => [
                'route'   => 'popcorn/canyon[/...]',
                'handler' => '\\Tests\\Support\\Controllers\\Popcorn::canyon',
            ],
            9 => [
                'route'   => 'popcorn/cat[/...]',
                'handler' => '\\Tests\\Support\\Controllers\\Popcorn::cat',
            ],
            10 => [
                'route'   => 'popcorn/json[/...]',
                'handler' => '\\Tests\\Support\\Controllers\\Popcorn::json',
            ],
            11 => [
                'route'   => 'popcorn/xml[/...]',
                'handler' => '\\Tests\\Support\\Controllers\\Popcorn::xml',
            ],
            12 => [
                'route'   => 'popcorn/toindex[/...]',
                'handler' => '\\Tests\\Support\\Controllers\\Popcorn::toindex',
            ],
            13 => [
                'route'   => 'popcorn/echoJson[/...]',
                'handler' => '\\Tests\\Support\\Controllers\\Popcorn::echoJson',
            ],
        ];
        $this->assertSame($expected, $routes);
    }

    public function testReadControllerWithRemap(): void
    {
        $namespace = 'Tests\Support\Controllers';
        $reader    = new ControllerMethodReader($namespace);

        $routes = $reader->read(Remap::class);

        $expected = [
            0 => [
                'route'   => 'remap[/...]',
                'handler' => '\\Tests\\Support\\Controllers\\Remap::_remap',
            ],
        ];
        $this->assertSame($expected, $routes);
    }
}
