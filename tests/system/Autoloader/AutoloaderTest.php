<?php namespace CodeIgniter\Autoloader;

use Config\Autoload;
use Config\Modules;

class AutoloaderTest extends \CodeIgniter\Test\CIUnitTestCase
{
	/**
	 * @var \CodeIgniter\Autoloader\Autoloader
	 */
	protected $loader;

	protected $filesPath = SUPPORTPATH . 'Autoloader/';

	//--------------------------------------------------------------------

	protected function setUp(): void
	{
		parent::setUp();

		$config                           = new Autoload();
		$moduleConfig                     = new Modules();
		$moduleConfig->discoverInComposer = false;

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
		$this->loader->initialize($config, $moduleConfig)->register();
	}

	public function testLoadStoredClass()
	{
		$this->assertInstanceOf('UnnamespacedClass', new \UnnamespacedClass());
	}

	public function testInitializeWithInvalidArguments()
	{
		$this->expectException(\InvalidArgumentException::class);

		$config                           = new Autoload();
		$config->classmap                 = [];
		$config->psr4                     = [];
		$moduleConfig                     = new Modules();
		$moduleConfig->discoverInComposer = false;

		(new Autoloader())->initialize($config, $moduleConfig);
	}

	//--------------------------------------------------------------------
	// PSR4 Namespacing
	//--------------------------------------------------------------------

	public function testServiceAutoLoaderFromShareInstances()
	{
		$auto_loader = \CodeIgniter\Config\Services::autoloader();
		// $auto_loader->register();
		// look for Home controller, as that should be in base repo
		$actual   = $auto_loader->loadClass('App\Controllers\Home');
		$expected = APPPATH . 'Controllers/Home.php';
		$this->assertSame($expected, $actual);
	}

	//--------------------------------------------------------------------

	public function testServiceAutoLoader()
	{
		$getShared   = false;
		$auto_loader = \CodeIgniter\Config\Services::autoloader($getShared);
		$auto_loader->initialize(new Autoload(), new Modules());
		$auto_loader->register();
		// look for Home controller, as that should be in base repo
		$actual   = $auto_loader->loadClass('App\Controllers\Home');
		$expected = APPPATH . 'Controllers/Home.php';
		$this->assertSame($expected, $actual);
	}

	//--------------------------------------------------------------------

	public function testExistingFile()
	{
		$actual   = $this->loader->loadClass('App\Controllers\Home');
		$expected = APPPATH . 'Controllers/Home.php';
		$this->assertSame($expected, $actual);

		$actual   = $this->loader->loadClass('CodeIgniter\Helpers\array_helper');
		$expected = SYSTEMPATH . 'Helpers/array_helper.php';
		$this->assertSame($expected, $actual);
	}

	//--------------------------------------------------------------------

	public function testMatchesWithPreceedingSlash()
	{
		$actual   = $this->loader->loadClass('\App\Controllers\Home');
		$expected = APPPATH . 'Controllers/Home.php';
		$this->assertSame($expected, $actual);
	}

	//--------------------------------------------------------------------

	public function testMatchesWithFileExtension()
	{
		$actual   = $this->loader->loadClass('\App\Controllers\Home.php');
		$expected = APPPATH . 'Controllers/Home.php';
		$this->assertSame($expected, $actual);
	}

	//--------------------------------------------------------------------

	public function testMissingFile()
	{
		$this->assertFalse($this->loader->loadClass('\App\Missing\Classname'));
	}

	//--------------------------------------------------------------------

	public function testInitializeException()
	{
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage("Config array must contain either the 'psr4' key or the 'classmap' key.");

		$config                           = new Autoload();
		$config->classmap                 = [];
		$config->psr4                     = [];
		$moduleConfig                     = new Modules();
		$moduleConfig->discoverInComposer = false;

		$this->loader = new Autoloader();
		$this->loader->initialize($config, $moduleConfig);
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
		$this->loader->addNamespace('My\App', APPPATH . 'Config');
		$this->loader->addNamespace('My\App', __DIR__);

		$actual   = $this->loader->loadClass('My\App\App');
		$expected = APPPATH . 'Config/App.php';
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

	//--------------------------------------------------------------------

	public function testRemoveNamespace()
	{
		$this->loader->addNamespace('My\App', __DIR__);
		$this->assertSame(__FILE__, $this->loader->loadClass('My\App\AutoloaderTest'));

		$this->loader->removeNamespace('My\App');
		$this->assertFalse((bool) $this->loader->loadClass('My\App\AutoloaderTest'));
	}

	//--------------------------------------------------------------------

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

	//--------------------------------------------------------------------

	public function testSanitizationSimply()
	{
		$test     = '${../path}!#/to/some/file.php_';
		$expected = '/path/to/some/file.php';

		$this->assertEquals($expected, $this->loader->sanitizeFilename($test));
	}

	//--------------------------------------------------------------------

	public function testSanitizationAllowsWindowsFilepaths()
	{
		$test = 'C:\path\to\some/file.php';

		$this->assertEquals($test, $this->loader->sanitizeFilename($test));
	}

	//--------------------------------------------------------------------

	public function testFindsComposerRoutes()
	{
		$config                           = new Autoload();
		$moduleConfig                     = new Modules();
		$moduleConfig->discoverInComposer = true;

		$this->loader = new Autoloader();
		$this->loader->initialize($config, $moduleConfig);

		$namespaces = $this->loader->getNamespace();
		$this->assertArrayHasKey('Laminas\\Escaper', $namespaces);
	}
}
