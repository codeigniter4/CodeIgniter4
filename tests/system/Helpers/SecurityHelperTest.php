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

namespace CodeIgniter\Helpers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockSecurity;
use Config\Security as SecurityConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Config\Services;

/**
 * @internal
 */
#[Group('Others')]
final class SecurityHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        helper('security');
    }

    public function testSanitizeFilenameSimpleSuccess(): void
    {
        Services::injectMock('security', new MockSecurity(new SecurityConfig()));

        $this->assertSame('hello.doc', sanitize_filename('hello.doc'));
    }

    public function testSanitizeFilenameStripsExtras(): void
    {
        $filename = './<!--foo -->';
        $this->assertSame('foo ', sanitize_filename($filename));
    }

    #[DataProvider('provideSanitizeFilenamePaths')]
    public function testSanitizeFilenamePaths(string $filename, bool $relativePath, string $expected): void
    {
        $this->assertSame($expected, sanitize_filename($filename, $relativePath));
    }

    /**
     * @return iterable<string, array{string, bool, string}>
     */
    public static function provideSanitizeFilenamePaths(): iterable
    {
        $backslash        = chr(92);
        $doubleBackslash  = str_repeat($backslash, 2);
        $tripleBackslash  = str_repeat($backslash, 3);
        $fourBackslashes  = str_repeat($backslash, 4);
        $eightBackslashes = str_repeat($backslash, 8);

        return [
            'empty filename'               => ['', false, ''],
            'escaped NUL in filename'      => ['a' . $backslash . '0b', false, 'a0b'],
            'escaped NUL in relative path' => ['a' . $backslash . '0b', true, 'a0b'],
            'literal NUL in filename'      => ['a' . chr(0) . 'b', false, 'ab'],
            'literal NUL in relative path' => ['a' . chr(0) . 'b', true, 'ab'],
            'strip forward path'           => [
                'approved/folder/file.txt',
                false,
                'approvedfolderfile.txt',
            ],
            'preserve forward relative path' => [
                'approved/folder/file.txt',
                true,
                'approved/folder/file.txt',
            ],
            'strip forward traversal'                => ['../../public/file.txt', false, 'publicfile.txt'],
            'relative forward traversal'             => ['../../public/file.txt', true, 'public/file.txt'],
            'forward traversal exposed by filters'   => ['.<.>/public/file.txt', true, 'public/file.txt'],
            'forward traversal split by backslash'   => ['.' . $backslash . './file.txt', true, 'file.txt'],
            'backslash traversal split by backslash' => [
                '.' . $backslash . '.' . $doubleBackslash . 'file.txt',
                true,
                '..file.txt',
            ],
            'mixed separators' => [
                '..' . $doubleBackslash . '../public' . $doubleBackslash . 'file.txt',
                true,
                '..publicfile.txt',
            ],
            'backslashes exposed by filters' => [
                'folder' . $backslash . '<' . $backslash . 'file.txt',
                true,
                'folderfile.txt',
            ],
            'filename traversal with single backslash' => [
                '..' . $backslash . '..' . $backslash . 'public' . $backslash . 'file.txt',
                false,
                '....publicfile.txt',
            ],
            'filename path with single backslash' => [
                'folder' . $backslash . 'file.txt',
                false,
                'folderfile.txt',
            ],
            'relative traversal with single backslash' => [
                '..' . $backslash . '..' . $backslash . 'public' . $backslash . 'file.txt',
                true,
                '....publicfile.txt',
            ],
            'relative path with single backslash' => [
                'folder' . $backslash . 'file.txt',
                true,
                'folderfile.txt',
            ],
            'filename traversal with double backslashes' => [
                '..' . $doubleBackslash . '..' . $doubleBackslash . 'public' . $doubleBackslash . 'file.txt',
                false,
                '....publicfile.txt',
            ],
            'filename path with double backslashes' => [
                'folder' . $doubleBackslash . 'file.txt',
                false,
                'folderfile.txt',
            ],
            'relative traversal with double backslashes' => [
                '..' . $doubleBackslash . '..' . $doubleBackslash . 'public' . $doubleBackslash . 'file.txt',
                true,
                '....publicfile.txt',
            ],
            'relative path with double backslashes' => [
                'folder' . $doubleBackslash . 'file.txt',
                true,
                'folderfile.txt',
            ],
            'filename traversal with triple backslashes' => [
                '..' . $tripleBackslash . '..' . $tripleBackslash . 'public' . $tripleBackslash . 'file.txt',
                false,
                '....publicfile.txt',
            ],
            'filename path with triple backslashes' => [
                'folder' . $tripleBackslash . 'file.txt',
                false,
                'folderfile.txt',
            ],
            'relative traversal with triple backslashes' => [
                '..' . $tripleBackslash . '..' . $tripleBackslash . 'public' . $tripleBackslash . 'file.txt',
                true,
                '....publicfile.txt',
            ],
            'relative path with triple backslashes' => [
                'folder' . $tripleBackslash . 'file.txt',
                true,
                'folderfile.txt',
            ],
            'filename traversal with four backslashes' => [
                '..' . $fourBackslashes . '..' . $fourBackslashes . 'public' . $fourBackslashes . 'file.txt',
                false,
                '....publicfile.txt',
            ],
            'filename path with four backslashes' => [
                'folder' . $fourBackslashes . 'file.txt',
                false,
                'folderfile.txt',
            ],
            'relative traversal with four backslashes' => [
                '..' . $fourBackslashes . '..' . $fourBackslashes . 'public' . $fourBackslashes . 'file.txt',
                true,
                '....publicfile.txt',
            ],
            'relative path with four backslashes' => [
                'folder' . $fourBackslashes . 'file.txt',
                true,
                'folderfile.txt',
            ],
            'filename traversal with eight backslashes' => [
                '..' . $eightBackslashes . '..' . $eightBackslashes . 'public' . $eightBackslashes . 'file.txt',
                false,
                '....publicfile.txt',
            ],
            'filename path with eight backslashes' => [
                'folder' . $eightBackslashes . 'file.txt',
                false,
                'folderfile.txt',
            ],
            'relative traversal with eight backslashes' => [
                '..' . $eightBackslashes . '..' . $eightBackslashes . 'public' . $eightBackslashes . 'file.txt',
                true,
                '....publicfile.txt',
            ],
            'relative path with eight backslashes' => [
                'folder' . $eightBackslashes . 'file.txt',
                true,
                'folderfile.txt',
            ],
        ];
    }

    public function testStripImageTags(): void
    {
        $this->assertSame('http://example.com/spacer.gif', strip_image_tags('http://example.com/spacer.gif'));

        $this->assertSame('http://example.com/spacer.gif', strip_image_tags('<img src="http://example.com/spacer.gif" alt="Who needs CSS when you have a spacer.gif?" />'));
    }

    public function testEncodePhpTags(): void
    {
        $this->assertSame('&lt;? echo $foo; ?&gt;', encode_php_tags('<? echo $foo; ?>'));
    }
}
