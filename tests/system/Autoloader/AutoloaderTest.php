<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Autoloader;

use App\Controllers\Home;
use Closure;
use CodeIgniter\Exceptions\ConfigException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ReflectionHelper;
use Config\Autoload;
use Config\Modules;
use Config\Services;
use InvalidArgumentException;
use RuntimeException;
use UnnamespacedClass;

/**
 * @internal
 *
 * @group Others
 */
final class AutoloaderTest extends CIUnitTestCase
{
    use ReflectionHelper;

    private Autoloader $loader;

    /**
     * @phpstan-var Closure(string): (false|string)
     */
    private Closure $classLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $config                      = new Autoload();
        $modules                     = new Modules();
        $modules->discoverInComposer = false;

        $config->classmap = [
            'UnnamespacedClass' => SUPPORTPATH . 'Autoloader/UnnamespacedClass.php',
            'OtherClass'        => APPPATH . 'Controllers/Home.php',
            'Name\Spaced\Class' => APPPATH . 'Controllers/Home.php',
        ];
        $config->psr4 = [
            'App'         => APPPATH,
            'CodeIgniter' => SYSTEMPATH,
        ];

        $this->loader = new Autoloader();
        $this->loader->initialize($config, $modules)->register();

        $this->classLoader = $this->getPrivateMethodInvoker($this->loader, 'loadInNamespace');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->loader->unregister();
    }

    public function testLoadStoredClass(): void
    {
        $this->assertInstanceOf('UnnamespacedClass', new UnnamespacedClass());
    }

    public function testInitializeWithInvalidArguments(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage("Config array must contain either the 'psr4' key or the 'classmap' key.");

        $config                      = new Autoload();
        $config->classmap            = [];
        $config->psr4                = [];
        $modules                     = new Modules();
        $modules->discoverInComposer = false;

        (new Autoloader())->initialize($config, $modules);
    }

    public function testInitializeTwice(): void
    {
        $loader = new Autoloader();
        $loader->initialize(new Autoload(), new Modules());

        $ns = $loader->getNamespace();
        $this->assertCount(1, $ns['App']);
        $this->assertSame('ROOTPATH/app', clean_path($ns['App'][0]));

        $loader->initialize(new Autoload(), new Modules());

        $ns = $loader->getNamespace();
        $this->assertCount(1, $ns['App']);
        $this->assertSame('ROOTPATH/app', clean_path($ns['App'][0]));
    }

    public function testServiceAutoLoaderFromShareInstances(): void
    {
        $classLoader = $this->getPrivateMethodInvoker(Services::autoloader(), 'loadInNamespace');

        // look for Home controller, as that should be in base repo
        $actual   = $classLoader(Home::class);
        $expected = APPPATH . 'Controllers' . DIRECTORY_SEPARATOR . 'Home.php';
        $this->assertSame($expected, realpath($actual) ?: $actual);
    }

    public function testServiceAutoLoader(): void
    {
        $autoloader = Services::autoloader(false);
        $autoloader->initialize(new Autoload(), new Modules());
        $autoloader->register();

        $classLoader = $this->getPrivateMethodInvoker($autoloader, 'loadInNamespace');

        // look for Home controller, as that should be in base repo
        $actual   = $classLoader(Home::class);
        $expected = APPPATH . 'Controllers' . DIRECTORY_SEPARATOR . 'Home.php';
        $this->assertSame($expected, realpath($actual) ?: $actual);

        $autoloader->unregister();
    }

    public function testExistingFile(): void
    {
        $actual   = ($this->classLoader)(Home::class);
        $expected = APPPATH . 'Controllers' . DIRECTORY_SEPARATOR . 'Home.php';
        $this->assertSame($expected, $actual);

        $actual   = ($this->classLoader)('CodeIgniter\Helpers\array_helper');
        $expected = SYSTEMPATH . 'Helpers' . DIRECTORY_SEPARATOR . 'array_helper.php';
        $this->assertSame($expected, $actual);
    }

    public function testMatchesWithPrecedingSlash(): void
    {
        $actual   = ($this->classLoader)(Home::class);
        $expected = APPPATH . 'Controllers' . DIRECTORY_SEPARATOR . 'Home.php';
        $this->assertSame($expected, $actual);
    }

    public function testMissingFile(): void
    {
        $this->assertFalse(($this->classLoader)('\App\Missing\Classname'));
    }

    public function testAddNamespaceWorks(): void
    {
        $this->assertFalse(($this->classLoader)('My\App\Class'));

        $this->loader->addNamespace('My\App', __DIR__);

        $actual   = ($this->classLoader)('My\App\AutoloaderTest');
        $expected = __FILE__;

        $this->assertSame($expected, $actual);
    }

    public function testAddNamespaceMultiplePathsWorks(): void
    {
        $this->loader->addNamespace([
            'My\App' => [
                APPPATH . 'Config',
                __DIR__,
            ],
        ]);

        $actual   = ($this->classLoader)('My\App\App');
        $expected = APPPATH . 'Config' . DIRECTORY_SEPARATOR . 'App.php';
        $this->assertSame($expected, $actual);

        $actual   = ($this->classLoader)('My\App\AutoloaderTest');
        $expected = __FILE__;
        $this->assertSame($expected, $actual);
    }

    public function testAddNamespaceStringToArray(): void
    {
        $this->loader->addNamespace('App\Controllers', __DIR__);

        $this->assertSame(
            __FILE__,
            ($this->classLoader)('App\Controllers\AutoloaderTest')
        );
    }

    public function testGetNamespaceGivesArray(): void
    {
        $this->assertSame([
            'App'         => [APPPATH],
            'CodeIgniter' => [SYSTEMPATH],
        ], $this->loader->getNamespace());

        $this->assertSame([SYSTEMPATH], $this->loader->getNamespace('CodeIgniter'));
        $this->assertSame([], $this->loader->getNamespace('Foo'));
    }

    public function testRemoveNamespace(): void
    {
        $this->loader->addNamespace('My\App', __DIR__);
        $this->assertSame(__FILE__, ($this->classLoader)('My\App\AutoloaderTest'));

        $this->loader->removeNamespace('My\App');
        $this->assertFalse(($this->classLoader)('My\App\AutoloaderTest'));
    }

    public function testloadClassNonNamespaced(): void
    {
        $this->assertFalse(($this->classLoader)('Modules'));
    }

    public function testSanitizationContailsSpecialChars(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The file path contains special characters "${}!#" that are not allowed: "${../path}!#/to/some/file.php_"'
        );

        $test = '${../path}!#/to/some/file.php_';

        $this->loader->sanitizeFilename($test);
    }

    public function testSanitizationFilenameEdges(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The characters ".-_" are not allowed in filename edges: "/path/to/some/file.php_"'
        );

        $test = '/path/to/some/file.php_';

        $this->loader->sanitizeFilename($test);
    }

    public function testSanitizationRegexError(): void
    {
        $this->expectException(RuntimeException::class);

        $test = mb_convert_encoding('クラスファイル.php', 'EUC-JP', 'UTF-8');

        $this->loader->sanitizeFilename($test);
    }

    public function testSanitizationAllowUnicodeChars(): void
    {
        $test = 'Ä/path/to/some/file.php';

        $this->assertSame($test, $this->loader->sanitizeFilename($test));
    }

    public function testSanitizationAllowsWindowsFilepaths(): void
    {
        $test = 'C:\path\to\some/file.php';

        $this->assertSame($test, $this->loader->sanitizeFilename($test));
    }

    public function testFindsComposerRoutes(): void
    {
        $config                      = new Autoload();
        $modules                     = new Modules();
        $modules->discoverInComposer = true;

        $loader = new Autoloader();
        $loader->initialize($config, $modules);

        $namespaces = $loader->getNamespace();
        $this->assertArrayHasKey('Laminas\\Escaper', $namespaces);
    }

    public function testComposerNamespaceDoesNotOverwriteConfigAutoloadPsr4(): void
    {
        $config       = new Autoload();
        $config->psr4 = [
            'Psr\Log' => '/Config/Autoload/Psr/Log/',
        ];
        $modules                     = new Modules();
        $modules->discoverInComposer = true;

        $loader = new Autoloader();
        $loader->initialize($config, $modules);

        $namespaces = $loader->getNamespace();
        $this->assertSame('/Config/Autoload/Psr/Log/', $namespaces['Psr\Log'][0]);
        $this->assertStringContainsString(VENDORPATH, $namespaces['Psr\Log'][1]);
    }

    public function testComposerPackagesOnly(): void
    {
        $config                      = new Autoload();
        $config->psr4                = [];
        $modules                     = new Modules();
        $modules->discoverInComposer = true;
        $modules->composerPackages   = ['only' => ['laminas/laminas-escaper']];

        $loader = new Autoloader();
        $loader->initialize($config, $modules);

        $namespaces = $loader->getNamespace();

        $this->assertCount(1, $namespaces);
        $this->assertStringContainsString(VENDORPATH, $namespaces['Laminas\Escaper'][0]);
    }

    public function testComposerPackagesExclude(): void
    {
        $config                      = new Autoload();
        $config->psr4                = [];
        $modules                     = new Modules();
        $modules->discoverInComposer = true;
        $modules->composerPackages   = [
            'exclude' => [
                'psr/log',
                'laminas/laminas-escaper',
            ],
        ];

        $loader = new Autoloader();
        $loader->initialize($config, $modules);

        $namespaces = $loader->getNamespace();

        $this->assertArrayNotHasKey('Psr\Log', $namespaces);
        $this->assertArrayNotHasKey('Laminas\\Escaper', $namespaces);
    }

    public function testComposerPackagesOnlyAndExclude(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Cannot use "only" and "exclude" at the same time in "Config\Modules::$composerPackages".');

        $config                      = new Autoload();
        $config->psr4                = [];
        $modules                     = new Modules();
        $modules->discoverInComposer = true;
        $modules->composerPackages   = [
            'only'    => ['laminas/laminas-escaper'],
            'exclude' => ['psr/log'],
        ];

        $loader = new Autoloader();
        $loader->initialize($config, $modules);
    }

    public function testFindsComposerRoutesWithComposerPathNotFound(): void
    {
        $composerPath = COMPOSER_PATH;

        $config                      = new Autoload();
        $modules                     = new Modules();
        $modules->discoverInComposer = true;

        $loader = new Autoloader();

        rename(COMPOSER_PATH, COMPOSER_PATH . '.backup');
        $loader->initialize($config, $modules);
        rename(COMPOSER_PATH . '.backup', $composerPath);

        $namespaces = $loader->getNamespace();
        $this->assertArrayNotHasKey('Laminas\\Escaper', $namespaces);
    }

    public function testAutoloaderLoadsNonClassFiles(): void
    {
        $config          = new Autoload();
        $config->files[] = SUPPORTPATH . 'Autoloader/functions.php';

        $loader = new Autoloader();
        $loader->initialize($config, new Modules());
        $loader->register();

        $this->assertTrue(function_exists('autoload_foo'));
        $this->assertSame('I am autoloaded by Autoloader through $files!', autoload_foo());
        $this->assertTrue(defined('AUTOLOAD_CONSTANT'));
        $this->assertSame('foo', AUTOLOAD_CONSTANT);

        $loader->unregister();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLoadHelpers(): void
    {
        $config            = new Autoload();
        $config->helpers[] = 'form';

        $loader = new Autoloader();
        $loader->initialize($config, new Modules());

        $loader->loadHelpers();

        $this->assertTrue(function_exists('form_open'));

        $loader->unregister();
    }
}
