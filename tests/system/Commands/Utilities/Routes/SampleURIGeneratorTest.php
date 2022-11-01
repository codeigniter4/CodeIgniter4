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

use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;
use Generator;

/**
 * @internal
 *
 * @group Others
 */
final class SampleURIGeneratorTest extends CIUnitTestCase
{
    /**
     * @dataProvider routeKeyProvider
     */
    public function testGet(string $routeKey, string $expected)
    {
        $generator = new SampleURIGenerator();

        $uri = $generator->get($routeKey);

        $this->assertSame($expected, $uri);
    }

    public function routeKeyProvider(): Generator
    {
        yield from [
            'root'                => ['/', '/'],
            'placeholder num'     => ['shop/product/([0-9]+)', 'shop/product/123'],
            'placeholder segment' => ['shop/product/([^/]+)', 'shop/product/abc_123'],
            'placeholder any'     => ['shop/product/(.*)', 'shop/product/123/abc'],
            'auto route'          => ['home/index[/...]', 'home/index/1/2/3/4/5'],
        ];
    }

    public function testGetFromPlaceholderCustomPlaceholder()
    {
        $routes = Services::routes();
        $routes->addPlaceholder(
            'uuid',
            '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'
        );

        $generator = new SampleURIGenerator();

        $routeKey = 'shop/product/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})';
        $uri      = $generator->get($routeKey);

        $this->assertSame('shop/product/::unknown::', $uri);
    }
}
