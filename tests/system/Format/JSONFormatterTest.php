<?php namespace CodeIgniter\Format;

class JSONFormatterTest extends \CIUnitTestCase
{
    protected $jsonFormatter;

    public function setUp()
    {
        parent::setUp();
        $this->jsonFormatter = new JSONFormatter();
    }

    public function testBasicJSON()
    {
        $data = [
            'foo' => 'bar'
        ];

        $expected = '{"foo":"bar"}';

        $this->assertEquals($expected, $this->jsonFormatter->format($data));
    }
}
