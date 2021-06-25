<?php

namespace CodeIgniter\Autoloader;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Autoload;
use Config\Modules;

/**
 * @internal
 */
final class FileLocatorTest extends CIUnitTestCase
{
    /**
     * @var FileLocator
     */
    protected $locator;

    //--------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();

        $autoloader = new Autoloader();
        $autoloader->initialize(new Autoload(), new Modules());
        $autoloader->addNamespace([
            'Unknown'       => '/i/do/not/exist',
            'Tests/Support' => TESTPATH . '_support/',
            'App'           => APPPATH,
            'CodeIgniter'   => [
                TESTPATH,
                SYSTEMPATH,
            ],
            'Errors' => APPPATH . 'Views/errors',
            'System' => SUPPORTPATH . 'Autoloader/system',
        ]);

        $this->locator = new FileLocator($autoloader);
    }

    //--------------------------------------------------------------------

    public function testLocateFileWorksWithLegacyStructure()
    {
        $file = 'Controllers/Home';

        $expected = APPPATH . 'Controllers/Home.php';

        $this->assertSame($expected, $this->locator->locateFile($file));
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

        $this->assertSame($expected, $this->locator->locateFile($file, 'Views'));
    }

    //--------------------------------------------------------------------

    public function testLocateFileWorksInApplicationDirectoryWithoutFolder()
    {
        $file = 'Common';

        $expected = APPPATH . 'Common.php';

        $this->assertSame($expected, $this->locator->locateFile($file));
    }

    //--------------------------------------------------------------------

    public function testLocateFileWorksInNestedApplicationDirectory()
    {
        $file = 'Controllers/Home';

        $expected = APPPATH . 'Controllers/Home.php';

        $this->assertSame($expected, $this->locator->locateFile($file, 'Controllers'));
    }

    //--------------------------------------------------------------------

    public function testLocateFileReplacesFolderName()
    {
        $file = '\App\Views/errors/html/error_404.php';

        $expected = APPPATH . 'Views/errors/html/error_404.php';

        $this->assertSame($expected, $this->locator->locateFile($file, 'Views'));
    }

    //--------------------------------------------------------------------

    public function testLocateFileReplacesFolderNameLegacy()
    {
        $file = 'Views/welcome_message.php';

        $expected = APPPATH . 'Views/welcome_message.php';

        $this->assertSame($expected, $this->locator->locateFile($file, 'Views'));
    }

    //--------------------------------------------------------------------

    public function testLocateFileCanFindNamespacedView()
    {
        $file = '\Errors\error_404';

        $expected = APPPATH . 'Views/errors/html/error_404.php';

        $this->assertSame($expected, $this->locator->locateFile($file, 'html'));
    }

    //--------------------------------------------------------------------

    public function testLocateFileCanFindNestedNamespacedView()
    {
        $file = '\Errors\html/error_404';

        $expected = APPPATH . 'Views/errors/html/error_404.php';

        $this->assertSame($expected, $this->locator->locateFile($file, 'html'));
    }

    //--------------------------------------------------------------------

    public function testLocateFileNotFoundExistingNamespace()
    {
        $file = '\App\Views/unexistence-file.php';

        $this->assertFalse($this->locator->locateFile($file, 'Views'));
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

        $this->assertSame($expected, $foundFiles[0]);
    }

    //--------------------------------------------------------------------

    public function testSearchWithFileExtension()
    {
        $expected = APPPATH . 'Config/App.php';

        $foundFiles = $this->locator->search('Config/App', 'php');

        $this->assertSame($expected, $foundFiles[0]);
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

    public function testSearchPrioritizeSystemOverApp()
    {
        $foundFiles = $this->locator->search('Language/en/Validation.php', 'php', false);

        $this->assertSame(
            [
                SYSTEMPATH . 'Language/en/Validation.php',
                APPPATH . 'Language/en/Validation.php',
            ],
            $foundFiles
        );
    }

    //--------------------------------------------------------------------

    public function testListNamespaceFilesEmptyPrefixAndPath()
    {
        $this->assertEmpty($this->locator->listNamespaceFiles('', ''));
    }

    //--------------------------------------------------------------------

    public function testListFilesSimple()
    {
        $files = $this->locator->listFiles('Config/');

        $expectedWin = APPPATH . 'Config\App.php';
        $expectedLin = APPPATH . 'Config/App.php';
        $this->assertTrue(in_array($expectedWin, $files, true) || in_array($expectedLin, $files, true));
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
        $this->assertTrue(in_array($expectedWin, $files, true) || in_array($expectedLin, $files, true));

        $expectedWin = SYSTEMPATH . 'Filters\Filters.php';
        $expectedLin = SYSTEMPATH . 'Filters/Filters.php';
        $this->assertTrue(in_array($expectedWin, $files, true) || in_array($expectedLin, $files, true));
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

        $this->assertSame($expected, $ClassName);
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
        $this->assertSame(
            __CLASS__,
            $this->locator->getClassname(__FILE__)
        );
    }

    public function testGetClassNameFromNonClassFile()
    {
        $this->assertSame(
            '',
            $this->locator->getClassname(SYSTEMPATH . 'bootstrap.php')
        );
    }
}
