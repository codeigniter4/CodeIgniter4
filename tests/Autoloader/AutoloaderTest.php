<?php

require_once "system/Autoloader/Autoloader.php";

define('APPPATH', 'application/');

class MockAutoloaderClass extends \CodeIgniter\Autoloader\Autoloader
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

//--------------------------------------------------------------------

class AutoloaderTest extends \PHPUnit_Framework_TestCase
{

	protected $loader;

	//--------------------------------------------------------------------

	protected function setUp()
	{
		$config = [
			'classmap' => [
				'FirstClass'        => '/app/dir/First.php',
				'Name\Spaced\Class' => '/app/namespace/Class.php',
			],
			'psr4'     => [
				'App\Controllers' => '/application/controllers',
				'App\Libraries'   => '/application/somewhere',
			],
		];

		$this->loader = new MockAutoloaderClass($config);

		$this->loader->setFiles([
			'/application/controllers/Classname.php',
			'/application/somewhere/Classname.php',
			'/app/dir/First.php',
			'/app/namespace/Class.php',
		    '/my/app/Class.php',
		    'application/libraries/someLibrary.php',
		    'application/models/someModel.php',
		]);
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// PSR4 Namespacing
	//--------------------------------------------------------------------

	public function testExistingFile()
	{
		$actual   = $this->loader->loadClass('App\Controllers\Classname');
		$expected = '/application/controllers/Classname.php';
		$this->assertSame($expected, $actual);

		$actual   = $this->loader->loadClass('App\Libraries\Classname');
		$expected = '/application/somewhere/Classname.php';
		$this->assertSame($expected, $actual);
	}

	//--------------------------------------------------------------------

	public function testMatchesWithPreceedingSlash()
	{
		$actual   = $this->loader->loadClass('\App\Controllers\Classname');
		$expected = '/application/controllers/Classname.php';
		$this->assertSame($expected, $actual);
	}

	//--------------------------------------------------------------------

	public function testMatchesWithFileExtension()
	{
		$actual   = $this->loader->loadClass('\App\Controllers\Classname.php');
		$expected = '/application/controllers/Classname.php';
		$this->assertSame($expected, $actual);
	}

	//--------------------------------------------------------------------

	public function testMissingFile()
	{
		$this->assertFalse($this->loader->loadClass('App\Missing\Classname'));
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Classmaps
	//--------------------------------------------------------------------

	public function testExistingClassmapFile()
	{
		$actual   = $this->loader->loadClass('FirstClass');
		$expected = '/app/dir/First.php';
		$this->assertSame($expected, $actual);
	}

	//--------------------------------------------------------------------

	public function testExistingClassmapFileWithNamespace()
	{
		$actual   = $this->loader->loadClass('Name\Spaced\Class');
		$expected = '/app/namespace/Class.php';

		$this->assertSame($expected, $actual);
	}

	//--------------------------------------------------------------------

	public function testAddNamespaceWorks()
	{
		$this->assertFalse($this->loader->loadClass('My\App\Class'));

		$this->loader->addNamespace('My\App', '/my/app');

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


}
