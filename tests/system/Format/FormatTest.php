<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Format;

use CodeIgniter\Format\Exceptions\FormatException;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 *
 * @group Others
 */
final class FormatTest extends CIUnitTestCase
{
    private Format $format;

    protected function setUp(): void
    {
        $this->format = new Format(new \Config\Format());
    }

    public function testFormatConfigType(): void
    {
        $config = new \Config\Format();
        $format = new Format($config);

        $this->assertInstanceOf(\Config\Format::class, $format->getConfig());
        $this->assertSame($config, $format->getConfig());
    }

    public function testGetFormatter(): void
    {
        $this->assertInstanceof(FormatterInterface::class, $this->format->getFormatter('application/json'));
    }

    public function testGetFormatterExpectsExceptionOnUndefinedMime(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('No Formatter defined for mime type: "application/x-httpd-php".');
        $this->format->getFormatter('application/x-httpd-php');
    }

    public function testGetFormatterExpectsExceptionOnUndefinedClass(): void
    {
        $this->format->getConfig()->formatters = array_merge(
            $this->format->getConfig()->formatters,
            ['text/xml' => 'App\Foo']
        );

        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('"App\Foo" is not a valid Formatter class.');
        $this->format->getFormatter('text/xml');
    }

    public function testGetFormatterExpectsExceptionOnClassNotImplementingFormatterInterface(): void
    {
        $this->format->getConfig()->formatters = array_merge(
            $this->format->getConfig()->formatters,
            ['text/xml' => URI::class]
        );

        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('"CodeIgniter\HTTP\URI" is not a valid Formatter class.');
        $this->format->getFormatter('text/xml');
    }
}
