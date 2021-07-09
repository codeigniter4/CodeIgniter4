<?php

namespace CodeIgniter\Test;

/**
 * Class BootstrapFCPATHTest
 *
 * This test confirms that the bootstrap.php
 * will set the correct FCPATH regardless of the current directory
 *
 * It writes a file in the temp directory that loads the bootstrap file
 * then compares its echo FCPATH; to the correct FCPATH returned
 * from correctFCPATH();
 *
 * @internal
 */
final class BootstrapFCPATHTest extends CIUnitTestCase
{
    private $currentDir = __DIR__;
    private $dir1       = '/tmp/dir1';
    private $file1      = '/tmp/dir1/testFile.php';

    protected function setUp(): void
    {
        parent::setUp();
        $this->deleteFiles();
        $this->deleteDirectories();
        $this->buildDirectories();
        $this->writeFiles();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->deleteFiles();
        $this->deleteDirectories();
    }

    public function testSetFCPATH()
    {
        $result1     = $this->readOutput($this->file1);
        $correctPath = $this->correctFCPATH();
        $this->assertSame($correctPath, $result1);
    }

    private function correctFCPATH()
    {
        return realpath(__DIR__ . '/../../../public') . DIRECTORY_SEPARATOR;
    }

    private function buildDirectories(): void
    {
        mkdir($this->dir1, 0777, true);
    }

    private function deleteDirectories(): void
    {
        // these need to be executed in reverse order: dir 2 in inside dir1
        if (is_dir($this->dir1)) {
            rmdir($this->dir1);
        }
    }

    private function writeFiles(): void
    {
        file_put_contents($this->file1, $this->fileContents());
        chmod($this->file1, 0777);
    }

    private function deleteFiles(): void
    {
        if (file_exists($this->file1)) {
            unlink($this->file1);
        }
    }

    private function fileContents()
    {
        $fileContents = '';
        $fileContents .= '<?php' . PHP_EOL;
        $fileContents .= "define('HOMEPATH', '" . $this->currentDir . "' . '/../../../');" . PHP_EOL;
        $fileContents .= "define('CONFIGPATH', '" . $this->currentDir . "' . '/../../../app/Config/');" . PHP_EOL;
        $fileContents .= "define('PUBLICPATH', '" . $this->currentDir . "' . '/../../../public/');" . PHP_EOL;
        $fileContents .= "include_once '" . $this->currentDir . "' . '/../../../vendor/autoload.php';" . PHP_EOL;
        $fileContents .= "include_once '" . $this->currentDir . "' . '/../../../system/Test/bootstrap.php';" . PHP_EOL;
        $fileContents .= '// return value of FCPATH' . PHP_EOL;
        $fileContents .= 'echo FCPATH;' . PHP_EOL;

        return $fileContents;
    }

    private function readOutput($file)
    {
        ob_start();
        system('php -f ' . $file);

        return ob_get_clean();
    }
}
