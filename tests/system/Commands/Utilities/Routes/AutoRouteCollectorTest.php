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

/**
 * @internal
 */
final class AutoRouteCollectorTest extends CIUnitTestCase
{
    public function testGet()
    {
        $namespace = 'Tests\Support\Controllers';
        $collector = new AutoRouteCollector(
            $namespace,
            'Home',
            'index',
        );

        $routes = $collector->get();

        $expected = [
            0 => [
                0 => 'auto',
                1 => 'hello',
                2 => '\\Tests\\Support\\Controllers\\Hello::index',
            ],
            1 => [
                0 => 'auto',
                1 => 'hello/index[/...]',
                2 => '\\Tests\\Support\\Controllers\\Hello::index',
            ],
            2 => [
                0 => 'auto',
                1 => 'popcorn',
                2 => '\\Tests\\Support\\Controllers\\Popcorn::index',
            ],
            3 => [
                0 => 'auto',
                1 => 'popcorn/index[/...]',
                2 => '\\Tests\\Support\\Controllers\\Popcorn::index',
            ],
            4 => [
                0 => 'auto',
                1 => 'popcorn/pop[/...]',
                2 => '\\Tests\\Support\\Controllers\\Popcorn::pop',
            ],
            5 => [
                0 => 'auto',
                1 => 'popcorn/popper[/...]',
                2 => '\\Tests\\Support\\Controllers\\Popcorn::popper',
            ],
            6 => [
                0 => 'auto',
                1 => 'popcorn/weasel[/...]',
                2 => '\\Tests\\Support\\Controllers\\Popcorn::weasel',
            ],
            7 => [
                0 => 'auto',
                1 => 'popcorn/oops[/...]',
                2 => '\\Tests\\Support\\Controllers\\Popcorn::oops',
            ],
            8 => [
                0 => 'auto',
                1 => 'popcorn/goaway[/...]',
                2 => '\\Tests\\Support\\Controllers\\Popcorn::goaway',
            ],
            9 => [
                0 => 'auto',
                1 => 'popcorn/index3[/...]',
                2 => '\\Tests\\Support\\Controllers\\Popcorn::index3',
            ],
            10 => [
                0 => 'auto',
                1 => 'popcorn/canyon[/...]',
                2 => '\\Tests\\Support\\Controllers\\Popcorn::canyon',
            ],
            11 => [
                0 => 'auto',
                1 => 'popcorn/cat[/...]',
                2 => '\\Tests\\Support\\Controllers\\Popcorn::cat',
            ],
            12 => [
                0 => 'auto',
                1 => 'popcorn/json[/...]',
                2 => '\\Tests\\Support\\Controllers\\Popcorn::json',
            ],
            13 => [
                0 => 'auto',
                1 => 'popcorn/xml[/...]',
                2 => '\\Tests\\Support\\Controllers\\Popcorn::xml',
            ],
            14 => [
                0 => 'auto',
                1 => 'popcorn/toindex[/...]',
                2 => '\\Tests\\Support\\Controllers\\Popcorn::toindex',
            ],
            15 => [
                0 => 'auto',
                1 => 'popcorn/echoJson[/...]',
                2 => '\\Tests\\Support\\Controllers\\Popcorn::echoJson',
            ],
            16 => [
                0 => 'auto',
                1 => 'remap[/...]',
                2 => '\\Tests\\Support\\Controllers\\Remap::_remap',
            ],
        ];
        $this->assertSame($expected, $routes);
    }
}
