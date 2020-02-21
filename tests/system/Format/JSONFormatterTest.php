<?php namespace CodeIgniter\Format;

class JSONFormatterTest extends \CodeIgniter\Test\CIUnitTestCase
{
	protected $jsonFormatter;

	protected function setUp(): void
	{
		parent::setUp();
		$this->jsonFormatter = new JSONFormatter();
	}

	public function testBasicJSON()
	{
		$data = [
			'foo' => 'bar',
		];

		$expected = '{
    "foo": "bar"
}';

		$this->assertEquals($expected, $this->jsonFormatter->format($data));
	}

	public function testUnicodeOutput()
	{
		$data = [
			'foo' => 'База данни грешка',
		];

		$expected = '{
    "foo": "База данни грешка"
}';

		$this->assertEquals($expected, $this->jsonFormatter->format($data));
	}

	public function testKeepsURLs()
	{
		$data = [
			'foo' => 'https://www.example.com/foo/bar',
		];

		$expected = '{
    "foo": "https://www.example.com/foo/bar"
}';

		$this->assertEquals($expected, $this->jsonFormatter->format($data));
	}

	public function testJSONError()
	{
		$this->expectException('RuntimeException');

		$data     = ["\xB1\x31"];
		$expected = 'Boom';
		$this->assertEquals($expected, $this->jsonFormatter->format($data));
	}

}
