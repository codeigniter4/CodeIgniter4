<?php namespace CodeIgniter\Autoloader;

use Config\MockAutoload;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

class FileLocatorTest extends \CIUnitTestCase
{

	/**
	 * @var MockFileLocator
	 */
	protected $loader;

	//--------------------------------------------------------------------

	public function setUp()
	{
		$config = new MockAutoload();
		$config->psr4 = [
			'App\Libraries'	 => '/application/somewhere',
			'App'			 => '/application',
			'Sys'			 => BASEPATH,
			'Blog'			 => '/modules/blog'
		];

		$this->loader = new MockFileLocator($config);

		$this->loader->setFiles([
			APPPATH . 'index.php',
			APPPATH . 'Views/index.php',
			APPPATH . 'Views/admin/users/create.php',
			'/modules/blog/Views/index.php',
			'/modules/blog/Views/admin/posts.php'
		]);
	}

	//--------------------------------------------------------------------

	public function testLocateFileWorksInApplicationDirectory()
	{
		$file = 'index';

		$expected = APPPATH . 'Views/index.php';

		$this->assertEquals($expected, $this->loader->locateFile($file, 'Views'));
	}

	//--------------------------------------------------------------------

	public function testLocateFileWorksInApplicationDirectoryWithoutFolder()
	{
		$file = 'index';

		$expected = APPPATH . 'index.php';

		$this->assertEquals($expected, $this->loader->locateFile($file));
	}

	//--------------------------------------------------------------------

	public function testLocateFileWorksInNestedApplicationDirectory()
	{
		$file = 'admin/users/create';

		$expected = APPPATH . 'Views/admin/users/create.php';

		$this->assertEquals($expected, $this->loader->locateFile($file, 'Views'));
	}

	//--------------------------------------------------------------------

	public function testLocateFileReplacesFolderName()
	{
		$file = '\Blog\Views/admin/posts.php';

		$expected = '/modules/blog/Views/admin/posts.php';

		$this->assertEquals($expected, $this->loader->locateFile($file, 'Views'));
	}

	//--------------------------------------------------------------------

	public function testLocateFileReplacesFolderNameLegacy()
	{
		$file = 'Views/index.php';

		$expected = APPPATH . 'Views/index.php';

		$this->assertEquals($expected, $this->loader->locateFile($file, 'Views'));
	}

	//--------------------------------------------------------------------

	public function testLocateFileCanFindNamespacedView()
	{
		$file = '\Blog\index';

		$expected = '/modules/blog/Views/index.php';

		$this->assertEquals($expected, $this->loader->locateFile($file, 'Views'));
	}

	//--------------------------------------------------------------------

	public function testLocateFileCanFindNestedNamespacedView()
	{
		$file = '\Blog\admin/posts.php';

		$expected = '/modules/blog/Views/admin/posts.php';

		$this->assertEquals($expected, $this->loader->locateFile($file, 'Views'));
	}

	//--------------------------------------------------------------------

	public function testLocateFileReturnsEmptyWithBadNamespace()
	{
		$file = '\Blogger\admin/posts.php';

		$this->assertEquals('', $this->loader->locateFile($file, 'Views'));
	}

	//--------------------------------------------------------------------

	public function testSearchSimple()
	{
		$expected = rtrim(APPPATH, '/') . '/Config/App.php';

		$foundFiles = $this->loader->search('Config/App.php');

		$this->assertEquals($expected, $foundFiles[0]);
	}

	//--------------------------------------------------------------------

	public function testSearchWithFileExtension()
	{
		$expected = rtrim(APPPATH, '/') . '/Config/App.php';

		$foundFiles = $this->loader->search('Config/App', 'php');

		$this->assertEquals($expected, $foundFiles[0]);
	}

	//--------------------------------------------------------------------

	public function testSearchWithMultipleFilesFound()
	{
		$foundFiles = $this->loader->search('index', 'html');

		$expected = rtrim(APPPATH, '/') . '/index.html';
		$this->assertTrue(in_array($expected, $foundFiles));

		$expected = rtrim(BASEPATH, '/') . '/index.html';
		$this->assertTrue(in_array($expected, $foundFiles));
	}

	//--------------------------------------------------------------------

	public function testSearchForFileNotExist()
	{
		$foundFiles = $this->loader->search('Views/Fake.html');

		$this->assertFalse(isset($foundFiles[0]));
	}

	//--------------------------------------------------------------------

	public function testListFilesSimple()
	{
		$files = $this->loader->listFiles('Config/');

		$expectedWin = APPPATH . 'Config\App.php';
		$expectedLin = APPPATH . 'Config/App.php';
		$this->assertTrue(in_array($expectedWin, $files) || in_array($expectedLin, $files));
	}

	//--------------------------------------------------------------------

	public function testListFilesWithFileAsInput()
	{
		$files = $this->loader->listFiles('Config/App.php');

		$this->assertTrue(empty($files));
	}

	//--------------------------------------------------------------------

	public function testListFilesFromMultipleDir()
	{
		$files = $this->loader->listFiles('Filters/');

		$expectedWin = APPPATH . 'Filters\DebugToolbar.php';
		$expectedLin = APPPATH . 'Filters/DebugToolbar.php';
		$this->assertTrue(in_array($expectedWin, $files) || in_array($expectedLin, $files));

		$expectedWin = BASEPATH . 'Filters\Filters.php';
		$expectedLin = BASEPATH . 'Filters/Filters.php';
		$this->assertTrue(in_array($expectedWin, $files) || in_array($expectedLin, $files));
	}

	//--------------------------------------------------------------------

	public function testListFilesWithPathNotExist()
	{
		$files = $this->loader->listFiles('Fake/');

		$this->assertTrue(empty($files));
	}

	//--------------------------------------------------------------------

	public function testListFilesWithoutPath()
	{
		$files = $this->loader->listFiles('');

		$this->assertTrue(empty($files));
	}

	public function testFindQNameFromPathSimple()
	{
		$ClassName = $this->loader->findQualifiedNameFromPath('system/HTTP/Header.php');
		$expected = '\Sys\HTTP\Header';

		$this->assertEquals($expected, $ClassName);
	}

	public function testFindQNameFromPathWithNumericNamespace()
	{
		$ClassName = $this->loader->findQualifiedNameFromPath('application/Config/App.php');

		$this->assertEquals(null, $ClassName);
	}

	public function testFindQNameFromPathWithFileNotExist()
	{
		$ClassName = $this->loader->findQualifiedNameFromPath('modules/blog/Views/index.php');

		$this->assertEquals(null, $ClassName);
	}

	public function testFindQNameFromPathWithoutCorrespondingNamespace()
	{
		$ClassName = $this->loader->findQualifiedNameFromPath('tests/system/CodeIgniterTest.php');

		$this->assertEquals(null, $ClassName);
	}

}
