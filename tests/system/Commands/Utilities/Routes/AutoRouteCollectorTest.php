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
 *
 * @group Others
 */
final class AutoRouteCollectorTest extends CIUnitTestCase
{
    public function testGet(): void
    {
        $namespace = 'Tests\Support\Controllers';
        $collector = new AutoRouteCollector(
            $namespace,
            'Home',
            'index',
        );

        $routes = $collector->get();

        $expected = [
            [
                'auto',
                'hello',
                '',
                '\\Tests\\Support\\Controllers\\Hello::index',
            ],
            [
                'auto',
                'hello/index[/...]',
                '',
                '\\Tests\\Support\\Controllers\\Hello::index',
            ],
            [
                'auto',
                'newautorouting/getIndex[/...]',
                '',
                '\Tests\Support\Controllers\Newautorouting::getIndex',
            ],
            [
                'auto',
                'newautorouting/postSave[/...]',
                '',
                '\Tests\Support\Controllers\Newautorouting::postSave',
            ],
            [
                'auto',
                'popcorn',
                '',
                '\\Tests\\Support\\Controllers\\Popcorn::index',
            ],
            [
                'auto',
                'popcorn/index[/...]',
                '',
                '\\Tests\\Support\\Controllers\\Popcorn::index',
            ],
            [
                'auto',
                'popcorn/pop[/...]',
                '',
                '\\Tests\\Support\\Controllers\\Popcorn::pop',
            ],
            [
                'auto',
                'popcorn/popper[/...]',
                '',
                '\\Tests\\Support\\Controllers\\Popcorn::popper',
            ],
            [
                'auto',
                'popcorn/weasel[/...]',
                '',
                '\\Tests\\Support\\Controllers\\Popcorn::weasel',
            ],
            [
                'auto',
                'popcorn/oops[/...]',
                '',
                '\\Tests\\Support\\Controllers\\Popcorn::oops',
            ],
            [
                'auto',
                'popcorn/goaway[/...]',
                '',
                '\\Tests\\Support\\Controllers\\Popcorn::goaway',
            ],
            [
                'auto',
                'popcorn/index3[/...]',
                '',
                '\\Tests\\Support\\Controllers\\Popcorn::index3',
            ],
            [
                'auto',
                'popcorn/canyon[/...]',
                '',
                '\\Tests\\Support\\Controllers\\Popcorn::canyon',
            ],
            [
                'auto',
                'popcorn/cat[/...]',
                '',
                '\\Tests\\Support\\Controllers\\Popcorn::cat',
            ],
            [
                'auto',
                'popcorn/json[/...]',
                '',
                '\\Tests\\Support\\Controllers\\Popcorn::json',
            ],
            [
                'auto',
                'popcorn/xml[/...]',
                '',
                '\\Tests\\Support\\Controllers\\Popcorn::xml',
            ],
            [
                'auto',
                'popcorn/toindex[/...]',
                '',
                '\\Tests\\Support\\Controllers\\Popcorn::toindex',
            ],
            [
                'auto',
                'popcorn/echoJson[/...]',
                '',
                '\\Tests\\Support\\Controllers\\Popcorn::echoJson',
            ],
            [
                'auto',
                'remap[/...]',
                '',
                '\\Tests\\Support\\Controllers\\Remap::_remap',
            ],
        ];
        $this->assertSame($expected, $routes);
    }
}
