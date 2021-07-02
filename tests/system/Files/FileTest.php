<?php

namespace CodeIgniter\Files;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class FileTest extends CIUnitTestCase
{
    public function testNewGoodChecked()
    {
        $path = SYSTEMPATH . 'Common.php';
        $file = new File($path, true);
        $this->assertSame($path, $file->getRealPath());
    }

    public function testNewGoodUnchecked()
    {
        $path = SYSTEMPATH . 'Common.php';
        $file = new File($path, false);
        $this->assertSame($path, $file->getRealPath());
    }

    public function testNewBadUnchecked()
    {
        $path = SYSTEMPATH . 'bogus';
        $file = new File($path, false);
        $this->assertFalse($file->getRealPath());
    }

    public function testGuessExtension()
    {
        $file = new File(SYSTEMPATH . 'Common.php');
        $this->assertSame('php', $file->guessExtension());
        $file = new File(SYSTEMPATH . 'index.html');
        $this->assertSame('html', $file->guessExtension());
        $file = new File(ROOTPATH . 'phpunit.xml.dist');
        $this->assertSame('xml', $file->guessExtension());
    }

    public function testRandomName()
    {
        $file    = new File(SYSTEMPATH . 'Common.php');
        $result1 = $file->getRandomName();
        $this->assertNotSame($result1, $file->getRandomName());
    }

    public function testCanAccessSplFileInfoMethods()
    {
        $file = new File(SYSTEMPATH . 'Common.php');
        $this->assertSame('file', $file->getType());
    }

    public function testGetSizeReturnsKB()
    {
        $file = new File(SYSTEMPATH . 'Common.php');
        $size = number_format(filesize(SYSTEMPATH . 'Common.php') / 1024, 3);
        $this->assertSame($size, $file->getSizeByUnit('kb'));
    }

    public function testGetSizeReturnsMB()
    {
        $file = new File(SYSTEMPATH . 'Common.php');
        $size = number_format(filesize(SYSTEMPATH . 'Common.php') / 1024 / 1024, 3);
        $this->assertSame($size, $file->getSizeByUnit('mb'));
    }

    public function testGetSizeReturnsBytes()
    {
        $file = new File(SYSTEMPATH . 'Common.php');
        $size = filesize(SYSTEMPATH . 'Common.php');
        $this->assertSame($size, $file->getSizeByUnit('b'));
    }

    public function testThrowsExceptionIfNotAFile()
    {
        $this->expectException('CodeIgniter\Files\Exceptions\FileNotFoundException');

        new File(SYSTEMPATH . 'Commoner.php', true);
    }
}
