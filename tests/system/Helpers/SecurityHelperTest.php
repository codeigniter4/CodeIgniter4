<?php

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
use Config\App;
use Tests\Support\Config\Services;

/**
 * @internal
 */
final class SecurityHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        helper('security');
    }

    public function testSanitizeFilenameSimpleSuccess()
    {
        Services::injectMock('security', new MockSecurity(new App()));

        $this->assertSame('hello.doc', sanitize_filename('hello.doc'));
    }

    public function testSanitizeFilenameStripsExtras()
    {
        $filename = './<!--foo -->';
        $this->assertSame('foo ', sanitize_filename($filename));
    }

    public function testStripImageTags()
    {
        $this->assertSame('http://example.com/spacer.gif', strip_image_tags('http://example.com/spacer.gif'));

        $this->assertSame('http://example.com/spacer.gif', strip_image_tags('<img src="http://example.com/spacer.gif" alt="Who needs CSS when you have a spacer.gif?" />'));
    }

    public function testEncodePhpTags()
    {
        $this->assertSame('&lt;? echo $foo; ?&gt;', encode_php_tags('<? echo $foo; ?>'));
    }
}
