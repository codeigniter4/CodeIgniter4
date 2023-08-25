<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\View;

use CodeIgniter\Autoloader\FileLocator;
use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\View\Exceptions\ViewException;
use Config;
use RuntimeException;

/**
 * @internal
 *
 * @group Others
 */
final class ViewTest extends CIUnitTestCase
{
    private FileLocator $loader;
    private string $viewsDir;
    private \Config\View $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader   = Services::locator();
        $this->viewsDir = __DIR__ . '/Views';
        $this->config   = new Config\View();
    }

    public function testSetVarStoresData(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('foo', 'bar');

        $this->assertSame(['foo' => 'bar'], $view->getData());
    }

    public function testSetVarOverwrites(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('foo', 'bar');
        $view->setVar('foo', 'baz');

        $this->assertSame(['foo' => 'baz'], $view->getData());
    }

    public function testSetDataStoresValue(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $expected = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $view->setData($expected);

        $this->assertSame($expected, $view->getData());
    }

    public function testSetDataMergesData(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $expected = [
            'fee' => 'fi',
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $view->setVar('fee', 'fi');
        $view->setData([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $this->assertSame($expected, $view->getData());
    }

    public function testSetDataOverwritesData(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $expected = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $view->setVar('foo', 'fi');
        $view->setData([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $this->assertSame($expected, $view->getData());
    }

    public function testSetVarWillEscape(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('foo', 'bar&', 'html');

        $this->assertSame(['foo' => 'bar&amp;'], $view->getData());
    }

    public function testSetDataWillEscapeAll(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $expected = [
            'foo' => 'bar&amp;',
            'bar' => 'baz&lt;',
        ];

        $view->setData([
            'foo' => 'bar&',
            'bar' => 'baz<',
        ], 'html');

        $this->assertSame($expected, $view->getData());
    }

    public function testRenderFindsView(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('testString', 'Hello World');
        $expected = '<h1>Hello World</h1>';

        $this->assertStringContainsString($expected, $view->render('simple'));
    }

    public function testRenderString(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('testString', 'Hello World');
        $expected = '<h1>Hello World</h1>';

        $this->assertSame($expected, $view->renderString('<h1><?= $testString ?></h1>'));
    }

    public function testRenderStringNullTempdata(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);
        $this->assertSame('test string', $view->renderString('test string'));
    }

    public function testRendersThrowsExceptionIfFileNotFound(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $this->expectException(ViewException::class);
        $view->setVar('testString', 'Hello World');

        $view->render('missing');
    }

    public function testRenderScrapsData(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('testString', 'Hello World');
        $view->render('simple', null, false);

        $this->assertEmpty($view->getData());
    }

    public function testRenderCanSaveData(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('testString', 'Hello World');
        $view->render('simple', null, true);

        $expected = ['testString' => 'Hello World'];

        $this->assertSame($expected, $view->getData());
    }

    public function testRenderCanSaveDataThroughConfigSetting(): void
    {
        $this->config->saveData = true;

        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('testString', 'Hello World');
        $view->render('simple');

        $expected = ['testString' => 'Hello World'];

        $this->assertSame($expected, $view->getData());
    }

    public function testCanDeleteData(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('testString', 'Hello World');
        $view->render('simple', null, true);

        $view->resetData();

        $this->assertSame([], $view->getData());
    }

    public function testCachedRender(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('testString', 'Hello World');
        $expected = '<h1>Hello World</h1>';

        $this->assertStringContainsString($expected, $view->render('simple', ['cache' => 10]));
        // this second renderings should go thru the cache
        $this->assertStringContainsString($expected, $view->render('simple', ['cache' => 10]));
    }

    public function testRenderStringSavingData(): void
    {
        $view     = new View($this->config, $this->viewsDir, $this->loader);
        $expected = '<h1>Hello World</h1>';

        // I think saveData is sava current data, is not clean already set data.
        $view->setVar('testString', 'Hello World');
        $this->assertSame($expected, $view->renderString('<h1><?= $testString ?></h1>', [], false));
        $this->assertArrayNotHasKey('testString', $view->getData());

        $view->setVar('testString', 'Hello World');
        $this->assertSame($expected, $view->renderString('<h1><?= $testString ?></h1>', [], true));
        $this->assertArrayHasKey('testString', $view->getData());
    }

    public function testPerformanceLogging(): void
    {
        // Make sure debugging is on for our view
        $view = new View($this->config, $this->viewsDir, $this->loader, true);
        $this->assertCount(0, $view->getPerformanceData());

        $view->setVar('testString', 'Hello World');
        $expected = '<h1>Hello World</h1>';
        $this->assertSame($expected, $view->renderString('<h1><?= $testString ?></h1>', [], true));
        $this->assertCount(1, $view->getPerformanceData());
    }

    public function testPerformanceNonLogging(): void
    {
        // Make sure debugging is on for our view
        $view = new View($this->config, $this->viewsDir, $this->loader, false);
        $this->assertCount(0, $view->getPerformanceData());

        $view->setVar('testString', 'Hello World');
        $expected = '<h1>Hello World</h1>';
        $this->assertSame($expected, $view->renderString('<h1><?= $testString ?></h1>', [], true));
        $this->assertCount(0, $view->getPerformanceData());
    }

    public function testRenderLayoutExtendsCorrectly(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('testString', 'Hello World');
        $expected = "<p>Open</p>\n<h1>Hello World</h1>";

        $this->assertStringContainsString($expected, $view->render('extend'));
    }

    public function testRenderLayoutExtendsMultipleCalls(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('testString', 'Hello World');
        $expected = "<p>Open</p>\n<h1>Hello World</h1>\n<p>Hello World</p>";

        $view->render('extend');

        $this->assertStringContainsString($expected, $view->render('extend'));
    }

    public function testRenderLayoutMakesDataAvailableToBoth(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('testString', 'Hello World');
        $expected = "<p>Open</p>\n<h1>Hello World</h1>\n<p>Hello World</p>";

        $this->assertStringContainsString($expected, $view->render('extend'));
    }

    public function testRenderLayoutSupportsMultipleOfSameSection(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('testString', 'Hello World');
        $expected = "<p>First</p>\n<p>Second</p>";

        $this->assertStringContainsString($expected, $view->render('extend_two'));
    }

    public function testRenderLayoutWithInclude(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('testString', 'Hello World');

        $content = $view->render('extend_include');

        $this->assertStringContainsString('<p>Open</p>', $content);
        $this->assertStringContainsString('<h1>Hello World</h1>', $content);
        $this->assertSame(2, substr_count($content, 'Hello World'));
    }

    public function testRenderLayoutBroken(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('testString', 'Hello World');
        $expected = '';

        $this->expectException(RuntimeException::class);
        $this->assertStringContainsString($expected, $view->render('broken'));
    }

    public function testRenderLayoutNoContentSection(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('testString', 'Hello World');
        $expected = '';

        $this->assertStringContainsString($expected, $view->render('apples'));
    }

    public function testRenderSaveDataCover(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);
        $this->setPrivateProperty($view, 'saveData', true);
        $view->setVar('testString', 'test');
        $view->render('simple', null, false);
        $this->assertTrue($this->getPrivateProperty($view, 'saveData'));
    }

    public function testRenderSaveDataUseAfterSaveDataFalse(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);
        $view->setVar('testString', 'test');
        $view->render('simple', null, true);
        $view->render('simple', null, false);
        $this->assertStringContainsString('<h1>test</h1>', $view->render('simple', null, false));
    }

    public function testCachedAutoDiscoverAndRender(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('testString', 'Hello World');
        $expected = '<h1>Hello World</h1>';

        $this->assertStringContainsString($expected, $view->render('Nested/simple', ['cache' => 10]));
        // this second renderings should go thru the cache
        $this->assertStringContainsString($expected, $view->render('Nested/simple', ['cache' => 10]));
    }

    public function testRenderNestedSections(): void
    {
        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('testString', 'Hello World');

        $content = $view->render('nested_section');

        $this->assertStringContainsString('<p>First</p>', $content);
        $this->assertStringContainsString('<p>Second</p>', $content);
        $this->assertStringContainsString('<p>Third</p>', $content);
    }

    public function testRenderSectionSavingData()
    {
        $view     = new View($this->config, $this->viewsDir, $this->loader);
        $expected = "<title>Welcome to CodeIgniter 4!</title>\n<h1>Welcome to CodeIgniter 4!</h1>\n<p>Hello World</p>";

        $view->setVar('pageTitle', 'Welcome to CodeIgniter 4!');
        $view->setVar('testString', 'Hello World');
        $this->assertStringContainsString($expected, $view->render('extend_reuse_section'));
    }
}
