<?php

namespace CodeIgniter\Autoloader;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Autoload;
use Config\Modules;
use Config\Services;

class AutoloaderTest extends CIUnitTestCase
{
	/**
	 * @var \CodeIgniter\Autoloader\Autoloader
	 */
	protected $loader;

	protected $filesPath = SUPPORTPATH . 'Autoloader/';

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
		$config->psr4     = [
			'App'         => APPPATH,
			'CodeIgniter' => SYSTEMPATH,
		];

		$this->loader = new Autoloader();
		$this->loader->initialize($config, $modules)->register();
	}

	public function testLoadStoredClass()
	{
		$this->assertInstanceOf('UnnamespacedClass', new \UnnamespacedClass());
	}

	public function testInitializeWithInvalidArguments()
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

	//--------------------------------------------------------------------
	// PSR4 Namespacing
	//--------------------------------------------------------------------

	public function testServiceAutoLoaderFromShareInstances()
	{
		$autoloader = Services::autoloader();
		// look for Home controller, as that should be in base repo
		$actual   = $autoloader->loadClass('App\Controllers\Home');
		$expected = APPPATH . 'Controllers' . DIRECTORY_SEPARATOR . 'Home.php';
		$this->assertSame($expected, $actual);
	}

	public function testServiceAutoLoader()
	{
		$autoloader = Services::autoloader(false);
		$autoloader->initialize(new Autoload(), new Modules());
		$autoloader->register();
		// look for Home controller, as that should be in base repo
		$actual   = $autoloader->loadClass('App\Controllers\Home');
		$expected = APPPATH . 'Controllers' . DIRECTORY_SEPARATOR . 'Home.php';
		$this->assertSame($expected, $actual);
	}

	public function testExistingFile()
	{
		$actual   = $this->loader->loadClass('App\Controllers\Home');
		$expected = APPPATH . 'Controllers' . DIRECTORY_SEPARATOR . 'Home.php';
		$this->assertSame($expected, $actual);

		$actual   = $this->loader->loadClass('CodeIgniter\Helpers\array_helper');
		$expected = SYSTEMPATH . 'Helpers' . DIRECTORY_SEPARATOR . 'array_helper.php';
		$this->assertSame($expected, $actual);
	}

	public function testMatchesWithPrecedingSlash()
	{
		$actual   = $this->loader->loadClass('\App\Controllers\Home');
		$expected = APPPATH . 'Controllers' . DIRECTORY_SEPARATOR . 'Home.php';
		$this->assertSame($expected, $actual);
	}

	public function testMatchesWithFileExtension()
	{
		$actual   = $this->loader->loadClass('\App\Controllers\Home.php');
		$expected = APPPATH . 'Controllers' . DIRECTORY_SEPARATOR . 'Home.php';
		$this->assertSame($expected, $actual);
	}

	public function testMissingFile()
	{
		$this->assertFalse($this->loader->loadClass('\App\Missing\Classname'));
	}

	public function testAddNamespaceWorks()
	{
		$this->assertFalse($this->loader->loadClass('My\App\Class'));

		$this->loader->addNamespace('My\App', __DIR__);

		$actual   = $this->loader->loadClass('My\App\AutoloaderTest');
		$expected = __FILE__;

		$this->assertSame($expected, $actual);
	}

	public function testAddNamespaceMultiplePathsWorks()
	{
		$this->loader->addNamespace([
			'My\App' => [
				APPPATH . 'Config',
				__DIR__,
			],
		]);

		$actual   = $this->loader->loadClass('My\App\App');
		$expected = APPPATH . 'Config' . DIRECTORY_SEPARATOR . 'App.php';
		$this->assertSame($expected, $actual);

		$actual   = $this->loader->loadClass('My\App\AutoloaderTest');
		$expected = __FILE__;
		$this->assertSame($expected, $actual);
	}

	public function testAddNamespaceStringToArray()
	{
		$this->loader->addNamespace('App\Controllers', __DIR__);

		$this->assertSame(
			__FILE__,
			$this->loader->loadClass('App\Controllers\AutoloaderTest')
		);
	}

	public function testGetNamespaceGivesArray()
	{
		$this->assertSame([
			'App'         => [APPPATH],
			'CodeIgniter' => [SYSTEMPATH],
		], $this->loader->getNamespace());

		$this->assertSame([SYSTEMPATH], $this->loader->getNamespace('CodeIgniter'));
		$this->assertSame([], $this->loader->getNamespace('Foo'));
	}

	public function testRemoveNamespace()
	{
		$this->loader->addNamespace('My\App', __DIR__);
		$this->assertSame(__FILE__, $this->loader->loadClass('My\App\AutoloaderTest'));

		$this->loader->removeNamespace('My\App');
		$this->assertFalse((bool) $this->loader->loadClass('My\App\AutoloaderTest'));
	}

	public function testloadClassConfigFound()
	{
		$this->loader->addNamespace('Config', APPPATH . 'Config');
		$this->assertSame(
			APPPATH . 'Config' . DIRECTORY_SEPARATOR . 'Modules.php',
			$this->loader->loadClass('Modules')
		);
	}

	public function testloadClassConfigNotFound()
	{
		$this->loader->addNamespace('Config', APPPATH . 'Config');
		$this->assertFalse($this->loader->loadClass('NotFound'));
	}

	public function testLoadLegacy()
	{
		// should not be able to find a folder
		$this->assertFalse((bool) $this->loader->loadClass(__DIR__));
		// should be able to find these because we said so in the Autoloader
		$this->assertTrue((bool) $this->loader->loadClass('Home'));
		// should not be able to find these - don't exist
		$this->assertFalse((bool) $this->loader->loadClass('anotherLibrary'));
		$this->assertFalse((bool) $this->loader->loadClass('\nester\anotherLibrary'));
		// should not be able to find these legacy classes - namespaced
		$this->assertFalse($this->loader->loadClass('Controllers\Home'));
	}

	public function testSanitizationSimply()
	{
		$test     = '${../path}!#/to/some/file.php_';
		$expected = '/path/to/some/file.php';

		$this->assertEquals($expected, $this->loader->sanitizeFilename($test));
	}

	public function testSanitizationAllowUnicodeChars()
	{
		$test     = 'Ä/path/to/some/file.php_';
		$expected = 'Ä/path/to/some/file.php';

		$this->assertEquals($expected, $this->loader->sanitizeFilename($test));
	}

	public function testSanitizationAllowsWindowsFilepaths()
	{
		$test = 'C:\path\to\some/file.php';

		$this->assertEquals($test, $this->loader->sanitizeFilename($test));
	}

	public function testFindsComposerRoutes()
	{
		$config                      = new Autoload();
		$modules                     = new Modules();
		$modules->discoverInComposer = true;

		$this->loader = new Autoloader();
		$this->loader->initialize($config, $modules);

		$namespaces = $this->loader->getNamespace();
		$this->assertArrayHasKey('Laminas\\Escaper', $namespaces);
	}

	public function testFindsComposerRoutesWithComposerPathNotFound()
	{
		$composerPath = COMPOSER_PATH;

		$config                      = new Autoload();
		$modules                     = new Modules();
		$modules->discoverInComposer = true;

		$this->loader = new Autoloader();

		rename(COMPOSER_PATH, COMPOSER_PATH . '.backup');
		$this->loader->initialize($config, $modules);
		rename(COMPOSER_PATH . '.backup', $composerPath);

		$namespaces = $this->loader->getNamespace();
		$this->assertArrayNotHasKey('Laminas\\Escaper', $namespaces);
	}
}
