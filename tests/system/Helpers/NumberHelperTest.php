<?php namespace CodeIgniter\HTTP;

class numberHelperTest extends \CIUnitTestCase
{
    public function __construct(...$params)
    {
        parent::__construct(...$params);

        helper('number');
    }

    //--------------------------------------------------------------------

    public function test_format_number()
    {
        $this->assertEquals('123,456', format_number(123456, 0, 'en_US'));
    }

    public function test_format_number_with_precision()
    {
        $this->assertEquals('123,456.8', format_number(123456.789, 1, 'en_US'));
        $this->assertEquals('123,456.79', format_number(123456.789, 2, 'en_US'));
    }

    public function test_number_to_size()
    {
        $this->assertEquals('456 Bytes', number_to_size(456));
    }

    public function test_kb_format()
    {
        $this->assertEquals('4.5 KB', number_to_size(4567));
    }

    public function test_kb_format_medium()
    {
        $this->assertEquals('44.6 KB', number_to_size(45678));
    }

    public function test_kb_format_large()
    {
        $this->assertEquals('446.1 KB', number_to_size(456789));
    }

    public function test_mb_format()
    {
        $this->assertEquals('3.3 MB', number_to_size(3456789));
    }

    public function test_gb_format()
    {
        $this->assertEquals('1.8 GB', number_to_size(1932735283.2));
    }

    public function test_tb_format()
    {
        $this->assertEquals('112,283.3 TB', number_to_size(123456789123456789));
    }

    public function test_thousands()
    {
        $this->assertEquals('123 thousand', number_to_amount('123,000', 0, 'en_US'));
    }

    public function test_millions()
    {
        $this->assertEquals('123.4 million', number_to_amount('123,400,000', 1, 'en_US'));
    }

    public function test_billions()
    {
        $this->assertEquals('123.46 billion', number_to_amount('123,456,000,000', 2, 'en_US'));
    }

    public function test_trillions()
    {
        $this->assertEquals('123.457 trillion', number_to_amount('123,456,700,000,000', 3, 'en_US'));
    }

    public function test_quadrillions()
    {
        $this->assertEquals('123.5 quadrillion', number_to_amount('123,456,700,000,000,000', 1, 'en_US'));
    }

    /**
     * @group single
     */
    public function test_currency_current_locale()
    {
        $this->assertEquals('$1,234.56', number_to_currency(1234.56, 'USD', 'en_US'));
        $this->assertEquals('£1,234.56', number_to_currency(1234.56, 'GBP', 'en_GB'));
    }

}
