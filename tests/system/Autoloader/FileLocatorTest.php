<?php namespace CodeIgniter\Autoloader;

use Config\Modules;

class FileLocatorTest extends \CIUnitTestCase
{
	/**
	 * @var \CodeIgniter\Autoloader\FileLocator
	 */
	protected $locator;

	//--------------------------------------------------------------------

	protected function setUp(): void
	{
		parent::setUp();

		$autoloader = new Autoloader();
		$autoloader->initialize(new \Config\Autoload(), new Modules());
		$autoloader->addNamespace([
			'Unknown'       => '/i/do/not/exist',
			'Tests/Support' => TESTPATH . '_support/',
			'App'           => APPPATH,
			'CodeIgniter'   => [
				SYSTEMPATH,
				TESTPATH,
			],
			'Errors'        => APPPATH . 'Views/errors',
			'System'        => SUPPORTPATH . 'Autoloader/system',
		]);

		$this->locator = new FileLocator($autoloader);
	}

	//--------------------------------------------------------------------

	public function testLocateFileWorksWithLegacyStructure()
	{
		$file = 'Controllers/Home';

		$expected = APPPATH . 'Controllers/Home.php';

		$this->assertEquals($expected, $this->locator->locateFile($file));
	}

	//--------------------------------------------------------------------

	public function testLocateFileWithLegacyStructureNotFound()
	{
		$file = 'Unknown';

		$this->assertFalse($this->locator->locateFile($file));
	}

	//--------------------------------------------------------------------

	public function testLocateFileWorksInApplicationDirectory()
	{
		$file = 'welcome_message';

		$expected = APPPATH . 'Views/welcome_message.php';

		$this->assertEquals($expected, $this->locator->locateFile($file, 'Views'));
	}

	//--------------------------------------------------------------------

	public function testLocateFileWorksInApplicationDirectoryWithoutFolder()
	{
		$file = 'bootstrap';

		$expected = SYSTEMPATH . 'bootstrap.php';

		$this->assertEquals($expected, $this->locator->locateFile($file));
	}

	//--------------------------------------------------------------------

	public function testLocateFileWorksInNestedApplicationDirectory()
	{
		$file = 'Controllers/Home';

		$expected = APPPATH . 'Controllers/Home.php';

		$this->assertEquals($expected, $this->locator->locateFile($file, 'Controllers'));
	}

	//--------------------------------------------------------------------

	public function testLocateFileReplacesFolderName()
	{
		$file = '\App\Views/errors/html/error_404.php';

		$expected = APPPATH . 'Views/errors/html/error_404.php';

		$this->assertEquals($expected, $this->locator->locateFile($file, 'Views'));
	}

	//--------------------------------------------------------------------

	public function testLocateFileReplacesFolderNameLegacy()
	{
		$file = 'Views/welcome_message.php';

		$expected = APPPATH . 'Views/welcome_message.php';

		$this->assertEquals($expected, $this->locator->locateFile($file, 'Views'));
	}

	//--------------------------------------------------------------------

	public function testLocateFileCanFindNamespacedView()
	{
		$file = '\Errors\error_404';

		$expected = APPPATH . 'Views/errors/html/error_404.php';

		$this->assertEquals($expected, $this->locator->locateFile($file, 'html'));
	}

	//--------------------------------------------------------------------

	public function testLocateFileCanFindNestedNamespacedView()
	{
		$file = '\Errors\html/error_404';

		$expected = APPPATH . 'Views/errors/html/error_404.php';

		$this->assertEquals($expected, $this->locator->locateFile($file, 'html'));
	}

	//--------------------------------------------------------------------

	public function testLocateFileNotFoundWithBadNamespace()
	{
		$file = '\Blogger\admin/posts.php';

		$this->assertFalse($this->locator->locateFile($file, 'Views'));
	}

	//--------------------------------------------------------------------

	public function testSearchSimple()
	{
		$expected = APPPATH . 'Config/App.php';

		$foundFiles = $this->locator->search('Config/App.php');

		$this->assertEquals($expected, $foundFiles[0]);
	}

	//--------------------------------------------------------------------

	public function testSearchWithFileExtension()
	{
		$expected = APPPATH . 'Config/App.php';

		$foundFiles = $this->locator->search('Config/App', 'php');

		$this->assertEquals($expected, $foundFiles[0]);
	}

	//--------------------------------------------------------------------

	public function testSearchWithMultipleFilesFound()
	{
		$foundFiles = $this->locator->search('index', 'html');

		$expected = APPPATH . 'index.html';
		$this->assertContains($expected, $foundFiles);

		$expected = SYSTEMPATH . 'index.html';

		$this->assertContains($expected, $foundFiles);
	}

	//--------------------------------------------------------------------

	public function testSearchForFileNotExist()
	{
		$foundFiles = $this->locator->search('Views/Fake.html');

		$this->assertArrayNotHasKey(0, $foundFiles);
	}

	//--------------------------------------------------------------------

	public function testListFilesSimple()
	{
		$files = $this->locator->listFiles('Config/');

		$expectedWin = APPPATH . 'Config\App.php';
		$expectedLin = APPPATH . 'Config/App.php';
		$this->assertTrue(in_array($expectedWin, $files) || in_array($expectedLin, $files));
	}

	//--------------------------------------------------------------------

	public function testListFilesWithFileAsInput()
	{
		$files = $this->locator->listFiles('Config/App.php');

		$this->assertEmpty($files);
	}

	//--------------------------------------------------------------------

	public function testListFilesFromMultipleDir()
	{
		$files = $this->locator->listFiles('Filters/');

		$expectedWin = SYSTEMPATH . 'Filters\DebugToolbar.php';
		$expectedLin = SYSTEMPATH . 'Filters/DebugToolbar.php';
		$this->assertTrue(in_array($expectedWin, $files) || in_array($expectedLin, $files));

		$expectedWin = SYSTEMPATH . 'Filters\Filters.php';
		$expectedLin = SYSTEMPATH . 'Filters/Filters.php';
		$this->assertTrue(in_array($expectedWin, $files) || in_array($expectedLin, $files));
	}

	//--------------------------------------------------------------------

	public function testListFilesWithPathNotExist()
	{
		$files = $this->locator->listFiles('Fake/');

		$this->assertEmpty($files);
	}

	//--------------------------------------------------------------------

	public function testListFilesWithoutPath()
	{
		$files = $this->locator->listFiles('');

		$this->assertEmpty($files);
	}

	public function testFindQNameFromPathSimple()
	{
		$ClassName = $this->locator->findQualifiedNameFromPath(SYSTEMPATH . 'HTTP/Header.php');
		$expected  = '\CodeIgniter\HTTP\Header';

		$this->assertEquals($expected, $ClassName);
	}

	public function testFindQNameFromPathWithFileNotExist()
	{
		$ClassName = $this->locator->findQualifiedNameFromPath('modules/blog/Views/index.php');

		$this->assertFalse($ClassName);
	}

	public function testFindQNameFromPathWithoutCorrespondingNamespace()
	{
		$ClassName = $this->locator->findQualifiedNameFromPath('/etc/hosts');

		$this->assertFalse($ClassName);
	}

	public function testGetClassNameFromClassFile()
	{
		$this->assertEquals(
			__CLASS__,
			$this->locator->getClassname(__FILE__)
		);
	}

	public function testGetClassNameFromNonClassFile()
	{
		$this->assertEquals(
			'',
			$this->locator->getClassname(SYSTEMPATH . 'bootstrap.php')
		);
	}

}
