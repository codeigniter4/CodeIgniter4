<?php namespace CodeIgniter\HTTP;

class HeaderTest extends \CodeIgniter\Test\CIUnitTestCase
{
	public function testHeaderStoresBasics()
	{
		$name  = 'foo';
		$value = 'bar';

		$header = new \CodeIgniter\HTTP\Header($name, $value);

		$this->assertEquals($name, $header->getName());
		$this->assertEquals($value, $header->getValue());
	}

	public function testHeaderStoresBasicsWithNull()
	{
		$name  = 'foo';
		$value = null;

		$header = new \CodeIgniter\HTTP\Header($name, $value);

		$this->assertEquals($name, $header->getName());
		$this->assertEquals('', $header->getValue());
	}

	//--------------------------------------------------------------------

	public function testHeaderStoresArrayValues()
	{
		$name  = 'foo';
		$value = [
			'bar',
			'baz',
		];

		$header = new \CodeIgniter\HTTP\Header($name, $value);

		$this->assertEquals($name, $header->getName());
		$this->assertEquals($value, $header->getValue());
	}

	//--------------------------------------------------------------------

	public function testHeaderSetters()
	{
		$name  = 'foo';
		$value = [
			'bar',
			'baz',
		];

				$header = new \CodeIgniter\HTTP\Header($name);
				$this->assertEquals($name, $header->getName());
				$this->assertEquals(null, $header->getValue());
				$this->assertEquals($name . ': ', (string) $header);

				$name = 'foo2';
		$header->setName($name)->setValue($value);
		$this->assertEquals($name, $header->getName());
		$this->assertEquals($value, $header->getValue());
				$this->assertEquals($name . ': bar, baz', (string) $header);
	}

	//--------------------------------------------------------------------

	public function testHeaderAppendsValueSkippedForNull()
	{
		$name     = 'foo';
		$value    = 'bar';
		$expected = 'bar';

		$header = new \CodeIgniter\HTTP\Header($name, $value);

		$header->appendValue(null);

		$this->assertEquals($name, $header->getName());
		$this->assertEquals($expected, $header->getValue());
	}

	public function testHeaderConvertsSingleToArray()
	{
		$name  = 'foo';
		$value = 'bar';

		$expected = [
			'bar',
			'baz',
		];

		$header = new \CodeIgniter\HTTP\Header($name, $value);

		$header->appendValue('baz');

		$this->assertEquals($name, $header->getName());
		$this->assertEquals($expected, $header->getValue());
	}

	//--------------------------------------------------------------------

	public function testHeaderPrependsValueSkippedForNull()
	{
		$name     = 'foo';
		$value    = 'bar';
		$expected = 'bar';

		$header = new \CodeIgniter\HTTP\Header($name, $value);

		$header->prependValue(null);

		$this->assertEquals($name, $header->getName());
		$this->assertEquals($expected, $header->getValue());
	}

	public function testHeaderPrependsValue()
	{
		$name  = 'foo';
		$value = 'bar';

		$expected = [
			'baz',
			'bar',
		];

		$header = new \CodeIgniter\HTTP\Header($name, $value);

		$header->prependValue('baz');

		$this->assertEquals($name, $header->getName());
		$this->assertEquals($expected, $header->getValue());
	}

	//--------------------------------------------------------------------

	public function testHeaderLineSimple()
	{
		$name  = 'foo';
		$value = [
			'bar',
			'baz',
		];

		$expected = 'bar, baz';

		$header = new \CodeIgniter\HTTP\Header($name, $value);

		$this->assertEquals($name, $header->getName());
		$this->assertEquals($expected, $header->getValueLine());
	}

	public function testHeaderLineValueNotStringOrArray()
	{
		$name  = 'foo';
		$value = new \stdClass;

		$expected = '';

		$header = new \CodeIgniter\HTTP\Header($name, $value);

		$this->assertEquals($name, $header->getName());
		$this->assertEquals($expected, $header->getValueLine());
	}

	//--------------------------------------------------------------------

	public function testHeaderSetValueWithNullWillMarkAsEmptyString()
	{
		$name     = 'foo';
		$expected = '';

		$header = new \CodeIgniter\HTTP\Header($name);
		$header->setValue('bar')
			   ->setValue(null);

		$this->assertEquals($name, $header->getName());
		$this->assertEquals($expected, $header->getValueLine());
	}

	public function testHeaderLineWithArrayValues()
	{
		$name = 'foo';

		$expected = 'bar, baz=fuzz';

		$header = new \CodeIgniter\HTTP\Header($name);

		$header->setValue('bar')
			   ->appendValue(['baz' => 'fuzz']);

		$this->assertEquals($name, $header->getName());
		$this->assertEquals($expected, $header->getValueLine());
	}

	//--------------------------------------------------------------------

	public function testHeaderToStringShowsEntireHeader()
	{
		$name = 'foo';

		$expected = 'foo: bar, baz=fuzz';

		$header = new \CodeIgniter\HTTP\Header($name);

		$header->setValue('bar')
			   ->appendValue(['baz' => 'fuzz']);

		$this->assertEquals($expected, (string)$header);
	}
}
