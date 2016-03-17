<?php namespace CodeIgniter\Autoloader;

class MockAutoloaderClass extends Autoloader
{

	protected $files = [];

	//--------------------------------------------------------------------

	public function setFiles($files)
	{
		$this->files = $files;
	}

	//--------------------------------------------------------------------

	protected function requireFile($file)
	{
		return in_array($file, $this->files) ? $file : false;
	}

	//--------------------------------------------------------------------


}

use Config\Autoload;

//--------------------------------------------------------------------

class AutoloaderTest extends \CIUnitTestCase
{

	protected $loader;

	//--------------------------------------------------------------------

	protected function setUp()
	{
		$config = new Autoload();

		$config->classmap = [
			'FirstClass'        => '/app/dir/First.php',
			'Name\Spaced\Class' => '/app/namespace/Class.php',
		];
		$config->psr4 = [
			'App\Controllers' => '/application/Controllers',
			'App\Libraries'   => '/application/somewhere',
		];

		$this->loader = new MockAutoloaderClass();
		$this->loader->initialize($config);

		$this->loader->setFiles([
			'/application/Controllers/Classname.php',
			'/application/somewhere/Classname.php',
			'/app/dir/First.php',
			'/app/namespace/Class.php',
		    '/my/app/Class.php',
		    APPPATH.'libraries/someLibrary.php',
		    APPPATH.'models/someModel.php',
		]);
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// PSR4 Namespacing
	//--------------------------------------------------------------------

	public function testExistingFile()
	{
		$actual   = $this->loader->loadClass('App\Controllers\Classname');
		$expected = '/application/Controllers/Classname.php';
		$this->assertSame($expected, $actual);

		$actual   = $this->loader->loadClass('App\Libraries\Classname');
		$expected = '/application/somewhere/Classname.php';
		$this->assertSame($expected, $actual);
	}

	//--------------------------------------------------------------------

	public function testMatchesWithPreceedingSlash()
	{
		$actual   = $this->loader->loadClass('\App\Controllers\Classname');
		$expected = '/application/Controllers/Classname.php';
		$this->assertSame($expected, $actual);
	}

	//--------------------------------------------------------------------

	public function testMatchesWithFileExtension()
	{
		$actual   = $this->loader->loadClass('\App\Controllers\Classname.php');
		$expected = '/application/Controllers/Classname.php';
		$this->assertSame($expected, $actual);
	}

	//--------------------------------------------------------------------

	public function testMissingFile()
	{
		$this->assertFalse($this->loader->loadClass('App\Missing\Classname'));
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------

	public function testAddNamespaceWorks()
	{
		$this->assertFalse($this->loader->loadClass('My\App\Class'));

		$this->loader->addNamespace('My\App', '/my/app');

		$actual = $this->loader->loadClass('My\App\Class');
		$expected = '/my/app/Class.php';

		$this->assertSame($expected, $actual);
	}

	public function testAddNamespaceMultiplePathsWorks()
	{
		$this->loader->addNamespace('My\App', '/my/app');
		$this->loader->addNamespace('My\App', '/test/app');
		$this->loader->setFiles([
			'/my/app/Class.php',
			'/test/app/ClassTest.php',
		]);

		$actual = $this->loader->loadClass('My\App\ClassTest');
		$expected = '/test/app/ClassTest.php';
		$this->assertSame($expected, $actual);

		$actual = $this->loader->loadClass('My\App\Class');
		$expected = '/my/app/Class.php';
		$this->assertSame($expected, $actual);
	}

	//--------------------------------------------------------------------

	public function testLoadLegacy()
	{
	    $this->assertFalse((bool)$this->loader->loadClass('someLibraries'));
	    $this->assertTrue((bool)$this->loader->loadClass('someLibrary'));
	    $this->assertTrue((bool)$this->loader->loadClass('someModel'));
	}

	//--------------------------------------------------------------------

	public function testSanitizationSimply()
	{
		$test = '${../path}!#:/to/some/file.php_';
		$expected = '/path/to/some/file.php';

		$this->assertEquals($expected, $this->loader->sanitizeFilename($test));
	}

	//--------------------------------------------------------------------


}
