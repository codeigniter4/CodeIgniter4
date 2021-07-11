<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

/**
 * @internal
 */
final class ConfigurableSortImportsTest extends CIUnitTestCase
{
    protected $streamFilter;

    protected function setUp(): void
    {
        parent::setUp();

        CITestStreamFilter::$buffer = '';

        $this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->streamFilter);
    }

    public function testPublishLanguageWithoutOptions()
    {
        command('publish:language');

        $file = APPPATH . 'Language/en/Foobar.php';
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertFileExists($file);
        $this->assertNotSame(sha1_file(SUPPORTPATH . 'Commands/Foobar.php'), sha1_file($file));
        if (is_file($file)) {
            unlink($file);
        }
    }

    public function testEnabledSortImportsWillDisruptLanguageFilePublish()
    {
        command('publish:language --lang es');

        $file = APPPATH . 'Language/es/Foobar.php';
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertFileExists($file);
        $this->assertNotSame(sha1_file(SUPPORTPATH . 'Commands/Foobar.php'), sha1_file($file));
        if (is_file($file)) {
            unlink($file);
        }
        $dir = dirname($file);
        if (is_dir($dir)) {
            rmdir($dir);
        }
    }

    public function testDisabledSortImportsWillNotAffectLanguageFilesPublish()
    {
        command('publish:language --lang ar --sort off');

        $file = APPPATH . 'Language/ar/Foobar.php';
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertFileExists($file);
        $this->assertSame(sha1_file(SUPPORTPATH . 'Commands/Foobar.php'), sha1_file($file));
        if (is_file($file)) {
            unlink($file);
        }
        $dir = dirname($file);
        if (is_dir($dir)) {
            rmdir($dir);
        }
    }
}
