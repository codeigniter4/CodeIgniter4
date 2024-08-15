<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class ConfigurableSortImportsTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    public function testPublishLanguageWithoutOptions(): void
    {
        command('publish:language');

        $file = APPPATH . 'Language' . DIRECTORY_SEPARATOR . 'en' . DIRECTORY_SEPARATOR . 'Foobar.php';
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());
        $this->assertFileExists($file);
        $this->assertNotSame(
            sha1_file(SUPPORTPATH . 'Commands' . DIRECTORY_SEPARATOR . 'Foobar.php'),
            sha1_file($file)
        );
        if (is_file($file)) {
            unlink($file);
        }
    }

    public function testEnabledSortImportsWillDisruptLanguageFilePublish(): void
    {
        command('publish:language --lang es');

        $file = APPPATH . 'Language' . DIRECTORY_SEPARATOR . 'es' . DIRECTORY_SEPARATOR . 'Foobar.php';
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());
        $this->assertFileExists($file);
        $this->assertNotSame(
            sha1_file(SUPPORTPATH . 'Commands' . DIRECTORY_SEPARATOR . 'Foobar.php'),
            sha1_file($file)
        );
        if (is_file($file)) {
            unlink($file);
        }
        $dir = dirname($file);
        if (is_dir($dir)) {
            rmdir($dir);
        }
    }

    public function testDisabledSortImportsWillNotAffectLanguageFilesPublish(): void
    {
        command('publish:language --lang ar --sort off');

        $file = APPPATH . 'Language' . DIRECTORY_SEPARATOR . 'ar' . DIRECTORY_SEPARATOR . 'Foobar.php';
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());
        $this->assertFileExists($file);
        $this->assertSame(sha1_file(SUPPORTPATH . 'Commands' . DIRECTORY_SEPARATOR . 'Foobar.php'), sha1_file($file));
        if (is_file($file)) {
            unlink($file);
        }
        $dir = dirname($file);
        if (is_dir($dir)) {
            rmdir($dir);
        }
    }
}
