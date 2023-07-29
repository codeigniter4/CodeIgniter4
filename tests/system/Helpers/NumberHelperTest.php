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

/**
 * @internal
 *
 * @group Others
 */
final class NumberHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        helper('number');
    }

    public function testRomanNumber(): void
    {
        $this->assertSame('XCVI', number_to_roman(96));
        $this->assertSame('MMDCCCXCV', number_to_roman(2895));
        $this->assertSame('CCCXXIX', number_to_roman(329));
        $this->assertSame('IV', number_to_roman(4));
        $this->assertSame('X', number_to_roman(10));
    }

    public function testRomanNumberRange(): void
    {
        $this->assertNull(number_to_roman(-1));
        $this->assertNull(number_to_roman(0));
        $this->assertNull(number_to_roman(4000));
    }

    public function testFormatNumber(): void
    {
        $this->assertSame('123,456', format_number(123456, 0, 'en_US'));
    }

    public function testFormatNumberWithPrecision(): void
    {
        $this->assertSame('123,456.8', format_number(123456.789, 1, 'en_US'));
        $this->assertSame('123,456.79', format_number(123456.789, 2, 'en_US'));
    }

    public function testFormattingOptions(): void
    {
        $options = [
            'before' => '<<',
            'after'  => '>>',
        ];
        $this->assertSame('<<123,456.79>>', format_number(123456.789, 2, 'en_US', $options));
    }

    public function testNumberToSize(): void
    {
        $this->assertSame('456 Bytes', number_to_size(456, 1, 'en_US'));
    }

    public function testKbFormat(): void
    {
        $this->assertSame('4.5 KB', number_to_size(4567, 1, 'en_US'));
    }

    public function testKbFormatMedium(): void
    {
        $this->assertSame('44.6 KB', number_to_size(45678, 1, 'en_US'));
    }

    public function testKbFormatLarge(): void
    {
        $this->assertSame('446.1 KB', number_to_size(456789, 1, 'en_US'));
    }

    public function testMbFormat(): void
    {
        $this->assertSame('3.3 MB', number_to_size(3_456_789, 1, 'en_US'));
    }

    public function testGbFormat(): void
    {
        $this->assertSame('1.8 GB', number_to_size(1_932_735_283.2, 1, 'en_US'));
    }

    public function testTbFormat(): void
    {
        $this->assertSame('112,283.3 TB', number_to_size(123_456_789_123_456_789, 1, 'en_US'));
    }

    public function testThousands(): void
    {
        $this->assertSame('123 thousand', number_to_amount('123,000', 0, 'en_US'));
        $this->assertSame('1 thousand', number_to_amount('1000', 0, 'en_US'));
        $this->assertSame('999 thousand', number_to_amount('999499', 0, 'en_US'));
        $this->assertSame('1,000 thousand', number_to_amount('999500', 0, 'en_US'));
        $this->assertSame('1,000 thousand', number_to_amount('999999', 0, 'en_US'));
    }

    public function testMillions(): void
    {
        $this->assertSame('123.4 million', number_to_amount('123,400,000', 1, 'en_US'));
        $this->assertSame('1 million', number_to_amount('1,000,000', 1, 'en_US'));
        $this->assertSame('1.5 million', number_to_amount('1,499,999', 1, 'en_US'));
        $this->assertSame('1.5 million', number_to_amount('1,500,000', 1, 'en_US'));
        $this->assertSame('1.5 million', number_to_amount('1,549,999', 1, 'en_US'));
        $this->assertSame('1.6 million', number_to_amount('1,550,000', 1, 'en_US'));
        $this->assertSame('999.5 million', number_to_amount('999,500,000', 1, 'en_US'));
        $this->assertSame('1,000 million', number_to_amount('999,500,000', 0, 'en_US'));
        $this->assertSame('1,000 million', number_to_amount('999,999,999', 1, 'en_US'));
    }

    public function testBillions(): void
    {
        $this->assertSame('123.46 billion', number_to_amount('123,456,000,000', 2, 'en_US'));
        $this->assertSame('1 billion', number_to_amount('1,000,000,000', 2, 'en_US'));
        $this->assertSame('1,000 billion', number_to_amount('999,999,999,999', 2, 'en_US'));
    }

    public function testTrillions(): void
    {
        $this->assertSame('123.457 trillion', number_to_amount('123,456,700,000,000', 3, 'en_US'));
        $this->assertSame('1 trillion', number_to_amount('1,000,000,000,000', 3, 'en_US'));
        $this->assertSame('1,000 trillion', number_to_amount('999,999,999,999,999', 3, 'en_US'));
    }

    public function testQuadrillions(): void
    {
        $this->assertSame('123.5 quadrillion', number_to_amount('123,456,700,000,000,000', 1, 'en_US'));
        $this->assertSame('1 quadrillion', number_to_amount('1,000,000,000,000,000', 0, 'en_US'));
        $this->assertSame('1,000 quadrillion', number_to_amount('999,999,999,999,999,999', 0, 'en_US'));
        $this->assertSame('1,000 quadrillion', number_to_amount('1,000,000,000,000,000,000', 0, 'en_US'));
    }

    public function testCurrencyCurrentLocale(): void
    {
        $this->assertSame('$1,235', number_to_currency(1234.56, 'USD', 'en_US'));
        $this->assertSame('$1,234.56', number_to_currency(1234.56, 'USD', 'en_US', 2));
        $this->assertSame('£1,234.56', number_to_currency(1234.56, 'GBP', 'en_GB', 2));
        $this->assertSame("1.234,56\u{a0}RSD", number_to_currency(1234.56, 'RSD', 'sr_RS', 2));
    }

    public function testNumbersThatArent(): void
    {
        $this->assertFalse(number_to_size('1232x'));
        $this->assertFalse(number_to_amount('1232x'));
    }
}
