<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Files;

use CodeIgniter\Files\Exceptions\FileNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use ZipArchive;

/**
 * @internal
 *
 * @group Others
 */
final class FileTest extends CIUnitTestCase
{
    public function testNewGoodChecked(): void
    {
        $path = SYSTEMPATH . 'Common.php';
        $file = new File($path, true);
        $this->assertSame($path, $file->getRealPath());
    }

    public function testNewGoodUnchecked(): void
    {
        $path = SYSTEMPATH . 'Common.php';
        $file = new File($path, false);
        $this->assertSame($path, $file->getRealPath());
    }

    public function testNewBadUnchecked(): void
    {
        $path = SYSTEMPATH . 'bogus';
        $file = new File($path, false);
        $this->assertFalse($file->getRealPath());
    }

    public function testGuessExtension(): void
    {
        $file = new File(SYSTEMPATH . 'Common.php');
        $this->assertSame('php', $file->guessExtension());

        $file = new File(SYSTEMPATH . 'index.html');
        $this->assertSame('html', $file->guessExtension());

        $file = new File(ROOTPATH . 'phpunit.xml.dist');
        $this->assertSame('xml', $file->guessExtension());

        $tmp  = tempnam(SUPPORTPATH, 'foo');
        $file = new File($tmp, true);
        $this->assertNull($file->guessExtension());
        unlink($tmp);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/6046
     */
    public function testGuessExtensionOnZip(): void
    {
        $tmp = SUPPORTPATH . 'foobar.zip';

        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::CREATE | ZipArchive::CHECKCONS | ZipArchive::EXCL);
        $zip->addFile(SYSTEMPATH . 'Common.php');
        $zip->close();

        $file = new File($tmp, true);
        $this->assertSame('zip', $file->guessExtension());
        unlink($tmp);
    }

    public function testRandomName(): void
    {
        $file    = new File(SYSTEMPATH . 'Common.php');
        $result1 = $file->getRandomName();
        $this->assertNotSame($result1, $file->getRandomName());
    }

    public function testCanAccessSplFileInfoMethods(): void
    {
        $file = new File(SYSTEMPATH . 'Common.php');
        $this->assertSame('file', $file->getType());
    }

    public function testGetSizeReturnsKB(): void
    {
        $file = new File(SYSTEMPATH . 'Common.php');
        $size = number_format(filesize(SYSTEMPATH . 'Common.php') / 1024, 3);
        $this->assertSame($size, $file->getSizeByUnit('kb'));
    }

    public function testGetSizeReturnsMB(): void
    {
        $file = new File(SYSTEMPATH . 'Common.php');
        $size = number_format(filesize(SYSTEMPATH . 'Common.php') / 1024 / 1024, 3);
        $this->assertSame($size, $file->getSizeByUnit('mb'));
    }

    public function testGetSizeReturnsBytes(): void
    {
        $file = new File(SYSTEMPATH . 'Common.php');
        $size = filesize(SYSTEMPATH . 'Common.php');
        $this->assertSame($size, $file->getSizeByUnit('b'));
    }

    public function testThrowsExceptionIfNotAFile(): void
    {
        $this->expectException(FileNotFoundException::class);

        new File(SYSTEMPATH . 'Commoner.php', true);
    }

    public function testGetDestination(): void
    {
        $file = new File(SYSTEMPATH . 'Common.php');
        copy(SYSTEMPATH . 'Common.php', SYSTEMPATH . 'Common_Copy.php');

        $this->assertSame(SYSTEMPATH . 'Common_Copy_1.php', $file->getDestination(SYSTEMPATH . 'Common_Copy.php', ''));
        $this->assertSame(SYSTEMPATH . 'Common_1.php', $file->getDestination(SYSTEMPATH . 'Common.php'));
        $this->assertSame(SYSTEMPATH . 'Common_Copy_5.php', $file->getDestination(SYSTEMPATH . 'Common_Copy_5.php'));

        copy(SYSTEMPATH . 'Common_Copy.php', SYSTEMPATH . 'Common_Copy_5.php');
        $this->assertSame(SYSTEMPATH . 'Common_Copy_6.php', $file->getDestination(SYSTEMPATH . 'Common_Copy_5.php'));

        unlink(SYSTEMPATH . 'Common_Copy.php');
        unlink(SYSTEMPATH . 'Common_Copy_5.php');
    }
}
