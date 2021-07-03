<?php

namespace CodeIgniter\Helpers;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class NumberHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        helper('number');
    }

    public function testRomanNumber()
    {
        $this->assertSame('XCVI', number_to_roman(96));
        $this->assertSame('MMDCCCXCV', number_to_roman(2895));
        $this->assertSame('CCCXXIX', number_to_roman(329));
        $this->assertSame('IV', number_to_roman(4));
        $this->assertSame('X', number_to_roman(10));
    }

    public function testRomanNumberRange()
    {
        $this->assertNull(number_to_roman(-1));
        $this->assertNull(number_to_roman(0));
        $this->assertNull(number_to_roman(4000));
    }

    public function testFormatNumber()
    {
        $this->assertSame('123,456', format_number(123456, 0, 'en_US'));
    }

    public function testFormatNumberWithPrecision()
    {
        $this->assertSame('123,456.8', format_number(123456.789, 1, 'en_US'));
        $this->assertSame('123,456.79', format_number(123456.789, 2, 'en_US'));
    }

    public function testFormattingOptions()
    {
        $options = [
            'before' => '<<',
            'after'  => '>>',
        ];
        $this->assertSame('<<123,456.79>>', format_number(123456.789, 2, 'en_US', $options));
    }

    public function testNumberToSize()
    {
        $this->assertSame('456 Bytes', number_to_size(456, 1, 'en_US'));
    }

    public function testKbFormat()
    {
        $this->assertSame('4.5 KB', number_to_size(4567, 1, 'en_US'));
    }

    public function testKbFormatMedium()
    {
        $this->assertSame('44.6 KB', number_to_size(45678, 1, 'en_US'));
    }

    public function testKbFormatLarge()
    {
        $this->assertSame('446.1 KB', number_to_size(456789, 1, 'en_US'));
    }

    public function testMbFormat()
    {
        $this->assertSame('3.3 MB', number_to_size(3456789, 1, 'en_US'));
    }

    public function testGbFormat()
    {
        $this->assertSame('1.8 GB', number_to_size(1932735283.2, 1, 'en_US'));
    }

    public function testTbFormat()
    {
        $this->assertSame('112,283.3 TB', number_to_size(123456789123456789, 1, 'en_US'));
    }

    public function testThousands()
    {
        $this->assertSame('123 thousand', number_to_amount('123,000', 0, 'en_US'));
    }

    public function testMillions()
    {
        $this->assertSame('123.4 million', number_to_amount('123,400,000', 1, 'en_US'));
    }

    public function testBillions()
    {
        $this->assertSame('123.46 billion', number_to_amount('123,456,000,000', 2, 'en_US'));
    }

    public function testTrillions()
    {
        $this->assertSame('123.457 trillion', number_to_amount('123,456,700,000,000', 3, 'en_US'));
    }

    public function testQuadrillions()
    {
        $this->assertSame('123.5 quadrillion', number_to_amount('123,456,700,000,000,000', 1, 'en_US'));
    }

    /**
     * @group single
     */
    public function testCurrencyCurrentLocale()
    {
        $this->assertSame('$1,234.56', number_to_currency(1234.56, 'USD', 'en_US', 2));
        $this->assertSame('£1,234.56', number_to_currency(1234.56, 'GBP', 'en_GB', 2));
        $this->assertSame("1.234,56\u{a0}RSD", number_to_currency(1234.56, 'RSD', 'sr_RS', 2));
    }

    public function testNumbersThatArent()
    {
        $this->assertFalse(number_to_size('1232x'));
        $this->assertFalse(number_to_amount('1232x'));
    }
}
