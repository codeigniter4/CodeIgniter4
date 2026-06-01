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

use CodeIgniter\Test\CIUnitTestCase;
use Config\Routing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class SampleURIGeneratorTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->resetServices();
    }

    #[DataProvider('provideGet')]
    public function testGet(string $routeKey, string $expected): void
    {
        $generator = new SampleURIGenerator();

        $uri = $generator->get($routeKey);

        $this->assertSame($expected, $uri);
    }

    public static function provideGet(): iterable
    {
        yield 'root' => ['/', '/'];

        yield 'placeholder num' => ['shop/product/([0-9]+)', 'shop/product/123'];

        yield 'placeholder segment' => ['shop/product/([^/]+)', 'shop/product/abc_123'];

        yield 'placeholder any' => ['shop/product/(.*)', 'shop/product/123/abc'];

        yield 'locale' => ['{locale}/home', 'en/home'];

        yield 'locale segment' => ['{locale}/product/([^/]+)', 'en/product/abc_123'];

        yield 'auto route' => ['home/index[/...]', 'home/index/1/2/3/4/5'];
    }

    public function testGetAutoGeneratesSampleForCustomPlaceholder(): void
    {
        $routes = service('routes');
        $routes->addPlaceholder('code', '[A-Z]{3}[0-9]+');

        $generator = new SampleURIGenerator();

        $uri = $generator->get('test/([A-Z]{3}[0-9]+)');

        $this->assertSame('test/AAA0', $uri);
    }

    public function testGetAutoGeneratesSampleForUuidPlaceholder(): void
    {
        $routes = service('routes');
        $routes->addPlaceholder(
            'uuid',
            '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}',
        );

        $generator = new SampleURIGenerator();

        $routeKey = 'shop/product/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})';
        $uri      = $generator->get($routeKey);

        $this->assertSame('shop/product/aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', $uri);
    }

    public function testGetUsesConfiguredSampleForCustomPlaceholder(): void
    {
        $routes = service('routes');
        $routes->addPlaceholder('code', '[A-Z]{3}[0-9]+');

        $config                     = new Routing();
        $config->placeholderSamples = ['code' => 'ABC123'];

        $generator = new SampleURIGenerator(null, $config);

        $uri = $generator->get('test/([A-Z]{3}[0-9]+)');

        $this->assertSame('test/ABC123', $uri);
    }

    public function testConfiguredSampleWinsOverAutoGeneration(): void
    {
        $routes = service('routes');
        $routes->addPlaceholder('code', '[A-Z]{3}[0-9]+');

        $config                     = new Routing();
        $config->placeholderSamples = ['code' => 'XYZ42'];

        $generator = new SampleURIGenerator(null, $config);

        $uri = $generator->get('test/([A-Z]{3}[0-9]+)');

        $this->assertSame('test/XYZ42', $uri);
    }

    public function testGetMemoizesSamplesAcrossCalls(): void
    {
        $routes = service('routes');
        $routes->addPlaceholder('code', '[A-Z]{3}[0-9]+');

        $generator = new SampleURIGenerator();

        $this->assertSame('test/AAA0', $generator->get('test/([A-Z]{3}[0-9]+)'));
        $this->assertSame('other/AAA0', $generator->get('other/([A-Z]{3}[0-9]+)'));
    }

    public function testGetFallsBackToUnknownForUnresolvablePlaceholder(): void
    {
        $routes = service('routes');
        // A lookahead is unsupported, so expect the fallback sentinel.
        $routes->addPlaceholder('guarded', '(?=abc)abc');

        $generator = new SampleURIGenerator();

        $uri = $generator->get('x/((?=abc)abc)');

        $this->assertSame('x/' . SampleURIGenerator::UNKNOWN_SAMPLE, $uri);
    }

    public function testConfiguredSampleWinsOverBuiltIn(): void
    {
        $config                     = new Routing();
        $config->placeholderSamples = ['num' => '987'];

        $generator = new SampleURIGenerator(null, $config);

        $uri = $generator->get('shop/product/([0-9]+)');

        $this->assertSame('shop/product/987', $uri);
    }

    public function testBuiltInSampleWinsOverAutoGeneration(): void
    {
        $generator = new SampleURIGenerator();

        // The built-in sample (123) is preferred over the value the regex
        // generator would produce for [0-9]+ (0).
        $this->assertSame('shop/product/123', $generator->get('shop/product/([0-9]+)'));
    }

    public function testNonMatchingConfiguredSampleIsIgnored(): void
    {
        $routes = service('routes');
        $routes->addPlaceholder('code', '[A-Z]{3}[0-9]+');

        $config                     = new Routing();
        $config->placeholderSamples = ['code' => 'does not match'];

        $generator = new SampleURIGenerator(null, $config);

        $uri = $generator->get('test/([A-Z]{3}[0-9]+)');

        $this->assertSame('test/AAA0', $uri);
    }

    public function testEmptyConfiguredSampleIsIgnored(): void
    {
        $routes = service('routes');
        $routes->addPlaceholder('code', '[A-Z]{3}[0-9]+');

        $config                     = new Routing();
        $config->placeholderSamples = ['code' => ''];

        $generator = new SampleURIGenerator(null, $config);

        $uri = $generator->get('test/([A-Z]{3}[0-9]+)');

        $this->assertSame('test/AAA0', $uri);
    }
}
