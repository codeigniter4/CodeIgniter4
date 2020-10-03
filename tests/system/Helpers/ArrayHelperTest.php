<?php namespace CodeIgniter\Helpers;

class ArrayHelperTest extends \CodeIgniter\Test\CIUnitTestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		helper('array');
	}

	public function testArrayDotSimple()
	{
		$data = [
			'foo' => [
				'bar' => 23,
			],
		];

		$this->assertEquals(23, dot_array_search('foo.bar', $data));
	}

	public function testArrayDotReturnNullEmptyArray()
	{
		$data = [];

		$this->assertNull(dot_array_search('foo.bar', $data));
	}

	public function testArrayDotReturnNullMissingValue()
	{
		$data = [
			'foo' => [
				'bar' => 23,
			],
		];

		$this->assertNull(dot_array_search('foo.baz', $data));
	}

	public function testArrayDotReturnNullEmptyIndex()
	{
		$data = [
			'foo' => [
				'bar' => 23,
			],
		];

		$this->assertNull(dot_array_search('', $data));
	}

	public function testArrayDotEarlyIndex()
	{
		$data = [
			'foo' => [
				'bar' => 23,
			],
		];

		$this->assertEquals(['bar' => 23], dot_array_search('foo', $data));
	}

	public function testArrayDotWildcard()
	{
		$data = [
			'foo' => [
				'bar' => [
					'baz' => 23,
				],
			],
		];

		$this->assertEquals(23, dot_array_search('foo.*.baz', $data));
	}

	public function testArrayDotWildcardWithMultipleChoices()
	{
		$data = [
			'foo' => [
				'buzz' => [
					'fizz' => 11,
				],
				'bar'  => [
					'baz' => 23,
				],
			],
		];

		$this->assertEquals(11, dot_array_search('foo.*.fizz', $data));
		$this->assertEquals(23, dot_array_search('foo.*.baz', $data));
	}

	public function testArrayDotNestedNotFound()
	{
		$data = [
			'foo' => [
				'buzz' => [
					'fizz' => 11,
				],
				'bar'  => [
					'baz' => 23,
				],
			],
		];

		$this->assertNull(dot_array_search('foo.*.notthere', $data));
	}

	public function testArrayDotIgnoresLastWildcard()
	{
		$data = [
			'foo' => [
				'bar' => [
					'baz' => 23,
				],
			],
		];

		$this->assertEquals(['baz' => 23], dot_array_search('foo.bar.*', $data));
	}

	/**
	 * @dataProvider deepSearchProvider
	 */
	public function testArrayDeepSearch($key, $expected)
	{
		$data = [
			'key1' => 'Value 1',
			'key5' => [
				'key51' => 'Value 5.1',
			],
			'key6' => [
				'key61' => [
					'key61' => 'Value 6.1',
					'key64' => [
						42       => 'Value 42',
						'key641' => 'Value 6.4.1',
						'key644' => [
							'key6441' => 'Value 6.4.4.1',
						],
					],
				],
			],
		];

		$result = array_deep_search($key, $data);

		$this->assertEquals($expected, $result);
	}

	public function testArrayDeepSearchReturnNullEmptyArray()
	{
		$data = [];

		$this->assertNull(array_deep_search('key644', $data));
	}

	//--------------------------------------------------------------------

	public function deepSearchProvider()
	{
		return [
			[
				'key6441',
				'Value 6.4.4.1',
			],
			[
				'key64421',
				null,
			],
			[
				42,
				'Value 42',
			],
			[
				'key644',
				['key6441' => 'Value 6.4.4.1'],
			],
			[
				'',
				null,
			],
		];
	}
}
