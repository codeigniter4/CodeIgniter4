<?php

require 'support/MockLoader.php';
require 'support/Config/MockAutoloadConfig.php';

class LoaderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var MockLoader
	 */
	protected $loader;

	//--------------------------------------------------------------------

	public function setUp()
	{
		$config = new MockAutoloadConfig();
		$config->psr4 = [
			'App\Libraries'   => '/application/somewhere',
			'App'             => '/application',
		    'Blog'            => '/modules/blog'
		];

	    $this->loader = new MockLoader($config);

		$this->loader->setFiles([
			APPPATH.'index.php',
			APPPATH.'views/index.php',
		    APPPATH.'views/admin/users/create.php',
		    '/modules/blog/views/index.php',
		    '/modules/blog/views/admin/posts.php'
		]);
	}

	//--------------------------------------------------------------------

	public function testLocateFileWorksInApplicationDirectory()
	{
		$file = 'index';

		$expected = APPPATH.'views/index.php';

		$this->assertEquals($expected, $this->loader->locateFile($file, 'views'));
	}

	//--------------------------------------------------------------------

	public function testLocateFileWorksInApplicationDirectoryWithoutFolder()
	{
		$file = 'index';

		$expected = APPPATH.'index.php';

		$this->assertEquals($expected, $this->loader->locateFile($file));
	}

	//--------------------------------------------------------------------

	public function testLocateFileWorksInNestedApplicationDirectory()
	{
		$file = 'admin/users/create';

		$expected = APPPATH.'views/admin/users/create.php';

		$this->assertEquals($expected, $this->loader->locateFile($file, 'views'));
	}

	//--------------------------------------------------------------------

	public function testLocateFileReplacesFolderName()
	{
		$file = '\Blog\views/admin/posts.php';

		$expected = '/modules/blog/views/admin/posts.php';

		$this->assertEquals($expected, $this->loader->locateFile($file, 'views'));
	}

	//--------------------------------------------------------------------

	public function testLocateFileReplacesFolderNameLegacy()
	{
		$file = 'views/index.php';

		$expected = APPPATH.'views/index.php';

		$this->assertEquals($expected, $this->loader->locateFile($file, 'views'));
	}

	//--------------------------------------------------------------------

	public function testLocateFileCanFindNamespacedView()
	{
	    $file = '\Blog\index';

		$expected = '/modules/blog/views/index.php';

		$this->assertEquals($expected, $this->loader->locateFile($file, 'views'));
	}

	//--------------------------------------------------------------------

	public function testLocateFileCanFindNestedNamespacedView()
	{
		$file = '\Blog\admin/posts.php';

		$expected = '/modules/blog/views/admin/posts.php';

		$this->assertEquals($expected, $this->loader->locateFile($file, 'views'));
	}

	//--------------------------------------------------------------------

	public function testLocateFileReturnsEmptyWithBadNamespace()
	{
		$file = '\Blogger\admin/posts.php';

		$this->assertEquals('', $this->loader->locateFile($file, 'views'));
	}

	//--------------------------------------------------------------------
}

