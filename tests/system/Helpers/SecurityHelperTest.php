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

        $this->resetServices();

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
        $this->assertSame('http://example.com/spacer.gif', strip_image_tags('<img src=\'http://example.com/spacer.gif\' alt="test" />'));
        $this->assertSame('http://example.com/spacer.gif', strip_image_tags('<img src=http://example.com/spacer.gif alt="test" />'));
        $this->assertSame('http://example.com/spacer.gif', strip_image_tags('<img src  =  "http://example.com/spacer.gif" />'));
        $this->assertSame('http://example.com/test.png', strip_image_tags('<img class="my-class" id="my-img" src="http://example.com/test.png">'));
        $this->assertSame('http://example.com/test.jpg', strip_image_tags('<img alt="test\'s photo" src="http://example.com/test.jpg">'));
        $this->assertSame('http://example.com/test.jpg', strip_image_tags('<img alt=\'test "quoted" photo\' src=\'http://example.com/test.jpg\'>'));

        // Empty src or tags without src should be stripped
        $this->assertSame('', strip_image_tags('<img src="" onerror="alert(1)">'));
        $this->assertSame('', strip_image_tags('<img src=\'\' onerror=\'alert(1)\'>'));
        $this->assertSame('', strip_image_tags('<img onerror="alert(1)">'));
        $this->assertSame('', strip_image_tags('<img alt="no image">'));
        $this->assertSame('Hello  World', strip_image_tags('Hello <img> World'));
        $this->assertSame('Hello  World', strip_image_tags('Hello <img/> World'));
        $this->assertSame('Hello  World', strip_image_tags('Hello <img > World'));

        // Multiline img tag
        $this->assertSame('http://example.com/test.png', strip_image_tags("<img\n  class=\"foo\"\n  src=\"http://example.com/test.png\"\n  alt=\"bar\">"));

        // Intervening text between multiple img tags must not be swallowed
        $this->assertSame('Text1  Text2 bar.jpg Text3', strip_image_tags('Text1 <img alt="foo"> Text2 <img src="bar.jpg"> Text3'));
        $this->assertSame('Start 1.jpg Middle 2.jpg End', strip_image_tags('Start <img src="1.jpg"> Middle <img src="2.jpg"> End'));

        // Attribute injection and quote handling
        $this->assertSame('test', strip_image_tags('<img src="test" onmouseover="alert(1)">'));
        $this->assertSame('test', strip_image_tags('<img src=\'test\' onmouseover=\'alert(1)\'>'));
        $this->assertSame('test', strip_image_tags('<img src=\'test\' onmouseover="alert(1)">'));
        $this->assertSame('test', strip_image_tags('<img src="test" onmouseover=\'alert(1)\'>'));

        // Nested img tag bypass
        $this->assertSame('x', strip_image_tags('<im<img>g src=x onerror=alert(1)>'));
        $this->assertSame('x', strip_image_tags('<im<im<img>g>g src=x onerror=alert(1)>'));

        // Only actual src attribute should be matched, not attributes ending with src (e.g. data-src)
        $this->assertSame('', strip_image_tags('<img data-src="http://example.com/fake.gif">'));
        $this->assertSame('http://example.com/real.gif', strip_image_tags('<img data-src="http://example.com/fake.gif" src="http://example.com/real.gif">'));
        $this->assertSame('http://example.com/real.gif', strip_image_tags('<img src="http://example.com/real.gif" data-src="http://example.com/fake.gif">'));
    }

    public function testStripImageTagsGreaterThanSignInsideQuotedAttribute(): void
    {
        $this->assertSame('photo.jpg', strip_image_tags('<img alt="><svg onload=alert(1)>" src="photo.jpg">'));
        $this->assertSame('photo.jpg', strip_image_tags("<img alt='><svg onload=alert(1)>' src='photo.jpg'>"));
        $this->assertSame('photo.jpg', strip_image_tags('<img src="photo.jpg" alt="><svg onload=alert(1)>">'));
        $this->assertSame('', strip_image_tags('<img alt="><svg onload=alert(1)>">'));
        $this->assertSame('Before photo.jpg After', strip_image_tags('Before <img alt="a>b" src="photo.jpg"> After'));
    }

    public function testStripImageTagsNestedTags(): void
    {
        $this->assertSame('x', strip_image_tags('<im<img>g src=x onerror=alert(1)>'));
        $this->assertSame('x', strip_image_tags('<im<im<img>g>g src=x onerror=alert(1)>'));
        $this->assertStringNotContainsString('<img', strip_image_tags('<im<img>g src=x onerror=alert(1)>'));
        $this->assertStringNotContainsString('onerror', strip_image_tags('<im<img>g src=x onerror=alert(1)>'));
    }

    public function testStripImageTagsIgnoresDataSrcAttribute(): void
    {
        $this->assertSame('real.gif', strip_image_tags('<img data-src="fake.gif" src="real.gif">'));
        $this->assertSame('real.gif', strip_image_tags('<img src="real.gif" data-src="fake.gif">'));
        $this->assertSame('', strip_image_tags('<img data-src="fake.gif">'));
    }

    public function testStripImageTagsWithoutSrcAttribute(): void
    {
        $this->assertSame('', strip_image_tags('<img>'));
        $this->assertSame('', strip_image_tags('<img alt="test">'));
        $this->assertSame('', strip_image_tags('<IMG ALT="test">'));
    }

    public function testStripImageTagsIsCaseInsensitive(): void
    {
        $this->assertSame('photo.jpg', strip_image_tags('<IMG SRC="photo.jpg">'));
    }

    public function testStripImageTagsFallbackWhenPcreFails(): void
    {
        $oldBacktrack = ini_get('pcre.backtrack_limit');
        $oldJit       = ini_get('pcre.jit');

        try {
            // Force PCRE failure: JIT does not honour backtrack_limit.
            ini_set('pcre.jit', '0');
            ini_set('pcre.backtrack_limit', '1');

            // Make sure PCRE really fails for this input, so the fallback is exercised.
            // This also keeps the test independent of the test order: a pattern
            // already compiled and JIT-cached by earlier tests still honours
            // backtrack_limit once JIT is disabled, and the assertion below turns
            // any silent PCRE success into an explicit failure.
            $input  = '<img src="photo.jpg" onerror="alert(1)">';
            $result = strip_image_tags($input);

            $this->assertStringNotContainsString('<img', $result);
            $this->assertStringContainsString('&lt;img', $result);

            $result = strip_image_tags('Hello <img alt="a>b" onerror=alert(1)> World');

            $this->assertSame('Hello &lt;img alt="a>b" onerror=alert(1)> World', $result);
        } finally {
            ini_set('pcre.backtrack_limit', (string) $oldBacktrack);
            ini_set('pcre.jit', (string) $oldJit);
        }
    }

    public function testStripImageTagsExceedingIterationLimitEscapesPrefix(): void
    {
        $input = str_repeat('<im', 26)
            . '<img>'
            . str_repeat('g>', 25)
            . 'g src=x onerror=alert(1)>';

        $output = strip_image_tags($input);

        $this->assertStringNotContainsString('<img', $output);
        $this->assertStringContainsString('&lt;img', $output);
    }

    public function testEncodePhpTags(): void
    {
        $this->assertSame('&lt;? echo $foo; ?&gt;', encode_php_tags('<? echo $foo; ?>'));
    }
}
