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

namespace CodeIgniter\View;

use CodeIgniter\Autoloader\FileLocatorInterface;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\View\Exceptions\ViewException;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\View\BadDecorator;
use Tests\Support\View\WorldDecorator;

/**
 * @internal
 */
#[Group('Others')]
final class DecoratorsTest extends CIUnitTestCase
{
    private FileLocatorInterface $loader;
    private string $viewsDir;
    private ?\Config\View $config = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader   = service('locator');
        $this->viewsDir = __DIR__ . '/Views';
        $this->config   = config('View');
    }

    public function testNoDecoratorsDoesntAlter(): void
    {
        $config             = $this->config;
        $config->decorators = [];
        Factories::injectMock('config', 'View', $config);

        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('testString', 'Hello World');
        $expected = '<h1>Hello World</h1>';

        $this->assertStringContainsString($expected, $view->render('simple'));
    }

    public function testThrowsOnInvalidClass(): void
    {
        $this->expectException(ViewException::class);
        $this->expectExceptionMessage(lang('View.invalidDecoratorClass', [BadDecorator::class]));

        $config             = $this->config;
        $config->decorators = [BadDecorator::class];
        Factories::injectMock('config', 'View', $config);

        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('testString', 'Hello World');
        $expected = '<h1>Hello World</h1>';

        $this->assertStringContainsString($expected, $view->render('simple'));
    }

    public function testDecoratorAltersOutput(): void
    {
        $config             = $this->config;
        $config->decorators = [WorldDecorator::class];
        Factories::injectMock('config', 'View', $config);

        $view = new View($this->config, $this->viewsDir, $this->loader);

        $view->setVar('testString', 'Hello World');
        $expected = '<h1>Hello Galaxy</h1>';

        $this->assertStringContainsString($expected, $view->render('simple'));
    }

    public function testParserNoDecoratorsDoesntAlter(): void
    {
        $config             = $this->config;
        $config->decorators = [];
        Factories::injectMock('config', 'View', $config);

        $parser = new Parser($this->config, $this->viewsDir, $this->loader);
        $parser->setVar('teststring', 'Hello World');

        $this->assertSame("<h1>Hello World</h1>\n", $parser->render('template1'));
    }

    public function testParserDecoratorAltersOutput(): void
    {
        $config             = $this->config;
        $config->decorators = [WorldDecorator::class];
        Factories::injectMock('config', 'View', $config);

        $parser = new Parser($this->config, $this->viewsDir, $this->loader);
        $parser->setVar('teststring', 'Hello World');

        $this->assertSame("<h1>Hello Galaxy</h1>\n", $parser->render('template1'));
    }
}
