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

    public function testLocateFileNotNamespacedFindsInAppDirectory(): void
    {
        $file = 'Controllers/Home'; // not namespaced

        $expected = APPPATH . 'Controllers/Home.php';

        $this->assertSame($expected, $this->locator->locateFile($file));
    }

    public function testLocateFileNotNamespacedNotFound(): void
    {
        $file = 'Unknown'; // not namespaced

        $this->assertFalse($this->locator->locateFile($file));
    }

    public function testLocateFileNotNamespacedFindsWithFolderInAppDirectory(): void
    {
        $file = 'welcome_message'; // not namespaced

        $expected = APPPATH . 'Views/welcome_message.php';

        $this->assertSame($expected, $this->locator->locateFile($file, 'Views'));
    }

    public function testLocateFileNotNamespacedFindesWithoutFolderInAppDirectory(): void
    {
        $file = 'Common'; // not namespaced

        $expected = APPPATH . 'Common.php';

        $this->assertSame($expected, $this->locator->locateFile($file));
    }

    public function testLocateFileNotNamespacedWorksInNestedAppDirectory(): void
    {
        $file = 'Controllers/Home'; // not namespaced

        $expected = APPPATH . 'Controllers/Home.php';

        // This works because $file contains `Controllers`.
        $this->assertSame($expected, $this->locator->locateFile($file, 'Controllers'));
    }

    public function testLocateFileWithFolderNameInFile(): void
    {
        $file = '\App\Views/errors/html/error_404.php';

        $expected = APPPATH . 'Views/errors/html/error_404.php';

        // This works because $file contains `Views`.
        $this->assertSame($expected, $this->locator->locateFile($file, 'Views'));
    }

    public function testLocateFileNotNamespacedWithFolderNameInFile(): void
    {
        $file = 'Views/welcome_message.php'; // not namespaced

        $expected = APPPATH . 'Views/welcome_message.php';

        // This works because $file contains `Views`.
        $this->assertSame($expected, $this->locator->locateFile($file, 'Views'));
    }

    public function testLocateFileCanFindNamespacedView(): void
    {
        $file = '\Errors\error_404';

        $expected = APPPATH . 'Views/errors/html/error_404.php';

        // The namespace `Errors` (APPPATH . 'Views/errors') + the folder (`html`) + `error_404`
        $this->assertSame($expected, $this->locator->locateFile($file, 'html'));
    }

    public function testLocateFileCanFindNestedNamespacedView(): void
    {
        $file = '\Errors\html/error_404';

        $expected = APPPATH . 'Views/errors/html/error_404.php';

        $this->assertSame($expected, $this->locator->locateFile($file, 'html'));
    }

    public function testLocateFileCanFindNamespacedViewWhenVendorHasTwoNamespaces(): void
    {
        $file = '\CodeIgniter\Devkit\View\Views/simple';

        $expected = ROOTPATH . 'tests/_support/View/Views/simple.php';

        $this->assertSame($expected, $this->locator->locateFile($file, 'Views'));
    }

    public function testLocateFileNotFoundExistingNamespace(): void
    {
        $file = '\App\Views/unexistence-file.php';

        $this->assertFalse($this->locator->locateFile($file, 'Views'));
    }

    public function testLocateFileNotFoundWithBadNamespace(): void
    {
        $file = '\Blogger\admin/posts.php';

        $this->assertFalse($this->locator->locateFile($file, 'Views'));
    }

    public function testLocateFileWithProperNamespace(): void
    {
        $file = 'Acme\SampleProject\View\Views\simple';

        $expected = ROOTPATH . 'tests/_support/View/Views/simple.php';

        $this->assertSame($expected, $this->locator->locateFile($file, 'Views'));
    }

    public function testSearchSimple(): void
    {
        $expected = APPPATH . 'Config/App.php';

        $foundFiles = $this->locator->search('Config/App.php');

        $this->assertSame($expected, $foundFiles[0]);
    }

    public function testSearchWithFileExtension(): void
    {
        $expected = APPPATH . 'Config/App.php';

        $foundFiles = $this->locator->search('Config/App', 'php');

        $this->assertSame($expected, $foundFiles[0]);
    }

    public function testSearchWithMultipleFilesFound(): void
    {
        $foundFiles = $this->locator->search('index', 'html');

        $expected = APPPATH . 'index.html';
        $this->assertContains($expected, $foundFiles);

        $expected = SYSTEMPATH . 'index.html';
        $this->assertContains($expected, $foundFiles);
    }

    public function testSearchForFileNotExist(): void
    {
        $foundFiles = $this->locator->search('Views/Fake.html');

        $this->assertArrayNotHasKey(0, $foundFiles);
    }

    public function testSearchPrioritizeSystemOverApp(): void
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

    public function testListNamespaceFilesEmptyPrefixAndPath(): void
    {
        $this->assertEmpty($this->locator->listNamespaceFiles('', ''));
    }

    public function testListFilesSimple(): void
    {
        $files = $this->locator->listFiles('Config/');

        $expectedWin = APPPATH . 'Config\App.php';
        $expectedLin = APPPATH . 'Config/App.php';
        $this->assertTrue(in_array($expectedWin, $files, true) || in_array($expectedLin, $files, true));
    }

    public function testListFilesDoesNotContainDirectories(): void
    {
        $files = $this->locator->listFiles('Config/');

        $directory = str_replace(
            '/',
            DIRECTORY_SEPARATOR,
            APPPATH . 'Config/Boot'
        );
        $this->assertNotContains($directory, $files);
    }

    public function testListFilesWithFileAsInput(): void
    {
        $files = $this->locator->listFiles('Config/App.php');

        $this->assertEmpty($files);
    }

    public function testListFilesFromMultipleDir(): void
    {
        $files = $this->locator->listFiles('Filters/');

        $expectedWin = SYSTEMPATH . 'Filters\DebugToolbar.php';
        $expectedLin = SYSTEMPATH . 'Filters/DebugToolbar.php';
        $this->assertTrue(in_array($expectedWin, $files, true) || in_array($expectedLin, $files, true));

        $expectedWin = SYSTEMPATH . 'Filters\Filters.php';
        $expectedLin = SYSTEMPATH . 'Filters/Filters.php';
        $this->assertTrue(in_array($expectedWin, $files, true) || in_array($expectedLin, $files, true));
    }

    public function testListFilesWithPathNotExist(): void
    {
        $files = $this->locator->listFiles('Fake/');

        $this->assertEmpty($files);
    }

    public function testListFilesWithoutPath(): void
    {
        $files = $this->locator->listFiles('');

        $this->assertEmpty($files);
    }

    public function testFindQNameFromPathSimple(): void
    {
        $ClassName = $this->locator->findQualifiedNameFromPath(SYSTEMPATH . 'HTTP/Header.php');
        $expected  = '\\' . Header::class;

        $this->assertSame($expected, $ClassName);
    }

    public function testFindQNameFromPathWithFileNotExist(): void
    {
        $ClassName = $this->locator->findQualifiedNameFromPath('modules/blog/Views/index.php');

        $this->assertFalse($ClassName);
    }

    public function testFindQNameFromPathWithoutCorrespondingNamespace(): void
    {
        $ClassName = $this->locator->findQualifiedNameFromPath('/etc/hosts');

        $this->assertFalse($ClassName);
    }

    public function testGetClassNameFromClassFile(): void
    {
        $this->assertSame(
            self::class,
            $this->locator->getClassname(__FILE__)
        );
    }

    public function testGetClassNameFromNonClassFile(): void
    {
        $this->assertSame(
            '',
            $this->locator->getClassname(SYSTEMPATH . 'bootstrap.php')
        );
    }
}
