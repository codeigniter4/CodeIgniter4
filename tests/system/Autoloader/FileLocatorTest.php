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

use CodeIgniter\HTTP\Header;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Autoload;
use Config\Modules;

/**
 * @internal
 *
 * @group Others
 */
final class FileLocatorTest extends CIUnitTestCase
{
    private FileLocator $locator;

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
            'Errors'              => APPPATH . 'Views/errors',
            'System'              => SUPPORTPATH . 'Autoloader/system',
            'CodeIgniter\\Devkit' => [
                TESTPATH . '_support/',
            ],
            'Acme\SampleProject' => TESTPATH . '_support',
            'Acme\Sample'        => TESTPATH . '_support/does/not/exists',
        ]);

        $this->locator = new FileLocator($autoloader);
    }

    public function testLocateFileNotNamespacedFindsInAppDirectory()
    {
        $file = 'Controllers/Home'; // not namespaced

        $expected = normalize_path(APPPATH . 'Controllers/Home.php');

        $this->assertSame($expected, $this->locator->locateFile($file));
    }

    public function testLocateFileNotNamespacedNotFound()
    {
        $file = 'Unknown'; // not namespaced

        $this->assertFalse($this->locator->locateFile($file));
    }

    public function testLocateFileNotNamespacedFindsWithFolderInAppDirectory()
    {
        $file = 'welcome_message'; // not namespaced

        $expected = normalize_path(APPPATH . 'Views/welcome_message.php');

        $this->assertSame($expected, $this->locator->locateFile($file, 'Views'));
    }

    public function testLocateFileNotNamespacedFindesWithoutFolderInAppDirectory()
    {
        $file = 'Common'; // not namespaced

        $expected = APPPATH . 'Common.php';

        $this->assertSame($expected, $this->locator->locateFile($file));
    }

    public function testLocateFileNotNamespacedWorksInNestedAppDirectory()
    {
        $file = 'Controllers/Home'; // not namespaced

        $expected = normalize_path(APPPATH . 'Controllers/Home.php');

        // This works because $file contains `Controllers`.
        $this->assertSame($expected, $this->locator->locateFile($file, 'Controllers'));
    }

    public function testLocateFileWithFolderNameInFile()
    {
        $file = '\App\Views/errors/html/error_404.php';

        $expected = normalize_path(APPPATH . 'Views/errors/html/error_404.php');

        // This works because $file contains `Views`.
        $this->assertSame($expected, $this->locator->locateFile($file, 'Views'));
    }

    public function testLocateFileNotNamespacedWithFolderNameInFile()
    {
        $file = 'Views/welcome_message.php'; // not namespaced

        $expected = normalize_path(APPPATH . 'Views/welcome_message.php');

        // This works because $file contains `Views`.
        $this->assertSame($expected, $this->locator->locateFile($file, 'Views'));
    }

    public function testLocateFileCanFindNamespacedView()
    {
        $file = '\Errors\error_404';

        $expected = normalize_path(APPPATH . 'Views/errors/html/error_404.php');

        // The namespace `Errors` (APPPATH . 'Views/errors') + the folder (`html`) + `error_404`
        $this->assertSame($expected, $this->locator->locateFile($file, 'html'));
    }

    public function testLocateFileCanFindNestedNamespacedView()
    {
        $file = '\Errors\html/error_404';

        $expected = normalize_path(APPPATH . 'Views/errors/html/error_404.php');

        $this->assertSame($expected, $this->locator->locateFile($file, 'html'));
    }

    public function testLocateFileCanFindNamespacedViewWhenVendorHasTwoNamespaces()
    {
        $file = '\CodeIgniter\Devkit\View\Views/simple';

        $expected = normalize_path(ROOTPATH . 'tests/_support/View/Views/simple.php');

        $this->assertSame($expected, $this->locator->locateFile($file, 'Views'));
    }

    public function testLocateFileNotFoundExistingNamespace()
    {
        $file = '\App\Views/unexistence-file.php';

        $this->assertFalse($this->locator->locateFile($file, 'Views'));
    }

    public function testLocateFileNotFoundWithBadNamespace()
    {
        $file = '\Blogger\admin/posts.php';

        $this->assertFalse($this->locator->locateFile($file, 'Views'));
    }

    public function testLocateFileWithProperNamespace()
    {
        $file = 'Acme\SampleProject\View\Views\simple';

        $expected = normalize_path(ROOTPATH . 'tests/_support/View/Views/simple.php');

        $this->assertSame($expected, $this->locator->locateFile($file, 'Views'));
    }

    public function testSearchSimple()
    {
        $expected = normalize_path(APPPATH . 'Config/App.php');

        $foundFiles = $this->locator->search('Config/App.php');

        $this->assertSame($expected, $foundFiles[0]);
    }

    public function testSearchWithFileExtension()
    {
        $expected = normalize_path(APPPATH . 'Config/App.php');

        $foundFiles = $this->locator->search('Config/App', 'php');

        $this->assertSame($expected, $foundFiles[0]);
    }

    public function testSearchWithMultipleFilesFound()
    {
        $foundFiles = $this->locator->search('index', 'html');

        $expected = APPPATH . 'index.html';
        $this->assertContains($expected, $foundFiles);

        $expected = SYSTEMPATH . 'index.html';
        $this->assertContains($expected, $foundFiles);
    }

    public function testSearchForFileNotExist()
    {
        $foundFiles = $this->locator->search('Views/Fake.html');

        $this->assertArrayNotHasKey(0, $foundFiles);
    }

    public function testSearchPrioritizeSystemOverApp()
    {
        $foundFiles = $this->locator->search('Language/en/Validation.php', 'php', false);

        $this->assertSame(
            [
                normalize_path(SYSTEMPATH . 'Language/en/Validation.php'),
                normalize_path(APPPATH . 'Language/en/Validation.php'),
            ],
            $foundFiles
        );
    }

    public function testListNamespaceFilesEmptyPrefixAndPath()
    {
        $this->assertEmpty($this->locator->listNamespaceFiles('', ''));
    }

    public function testListFilesSimple()
    {
        $files = $this->locator->listFiles('Config/');

        $expected = normalize_path(APPPATH . 'Config/App.php');
        $this->assertTrue(in_array($expected, $files, true));
    }

    public function testListFilesDoesNotContainDirectories()
    {
        $files = $this->locator->listFiles('Config/');

        $directory = str_replace(
            '/',
            DIRECTORY_SEPARATOR,
            APPPATH . 'Config/Boot'
        );
        $this->assertNotContains($directory, $files);
    }

    public function testListFilesWithFileAsInput()
    {
        $files = $this->locator->listFiles('Config/App.php');

        $this->assertEmpty($files);
    }

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

    public function testListFilesWithPathNotExist()
    {
        $files = $this->locator->listFiles('Fake/');

        $this->assertEmpty($files);
    }

    public function testListFilesWithoutPath()
    {
        $files = $this->locator->listFiles('');

        $this->assertEmpty($files);
    }

    public function testFindQNameFromPathSimple()
    {
        $ClassName = $this->locator->findQualifiedNameFromPath(SYSTEMPATH . 'HTTP/Header.php');
        $expected  = '\\' . Header::class;

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
            self::class,
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
