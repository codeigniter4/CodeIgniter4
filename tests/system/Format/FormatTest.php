<?php

namespace CodeIgniter\Format;

use CodeIgniter\Format\Exceptions\FormatException;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class FormatTest extends CIUnitTestCase
{
    /**
     * @var Format
     */
    protected $format;

    protected function setUp(): void
    {
        $this->format = new Format(new \Config\Format());
    }

    public function testFormatConfigType()
    {
        $config = new \Config\Format();
        $format = new Format($config);

        $this->assertInstanceOf('Config\Format', $format->getConfig());
        $this->assertSame($config, $format->getConfig());
    }

    public function testGetFormatter()
    {
        $this->assertInstanceof(FormatterInterface::class, $this->format->getFormatter('application/json'));
    }

    public function testGetFormatterExpectsExceptionOnUndefinedMime()
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('No Formatter defined for mime type: "application/x-httpd-php".');
        $this->format->getFormatter('application/x-httpd-php');
    }

    public function testGetFormatterExpectsExceptionOnUndefinedClass()
    {
        $this->format->getConfig()->formatters = array_merge(
            $this->format->getConfig()->formatters,
            ['text/xml' => 'App\Foo']
        );

        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('"App\Foo" is not a valid Formatter class.');
        $this->format->getFormatter('text/xml');
    }

    public function testGetFormatterExpectsExceptionOnClassNotImplementingFormatterInterface()
    {
        $this->format->getConfig()->formatters = array_merge(
            $this->format->getConfig()->formatters,
            ['text/xml' => 'CodeIgniter\HTTP\URI']
        );

        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('"CodeIgniter\HTTP\URI" is not a valid Formatter class.');
        $this->format->getFormatter('text/xml');
    }
}
