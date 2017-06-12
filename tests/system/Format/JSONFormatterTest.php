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

        $expected = '{
    "foo": "bar"
}';

        $this->assertEquals($expected, $this->jsonFormatter->format($data));
    }

	public function testUnicodeOutput()
	{
		$data = [
			'foo' => 'База данни грешка'
		];

		$expected = '{
    "foo": "База данни грешка"
}';

		$this->assertEquals($expected, $this->jsonFormatter->format($data));
    }

	public function testKeepsURLs()
	{
		$data = [
			'foo' => 'https://www.example.com/foo/bar'
		];

		$expected = '{
    "foo": "https://www.example.com/foo/bar"
}';

		$this->assertEquals($expected, $this->jsonFormatter->format($data));
	}

	public function testDoesNumericFormatting()
	{
		$data = [
			'foo' => '32',
			'bar' => 42,
			'baz' => "3.14"
		];

		$expected = '{
    "foo": 32,
    "bar": 42,
    "baz": 3.14
}';

		$this->assertEquals($expected, $this->jsonFormatter->format($data));
	}
}
