<?php namespace CodeIgniter\Helpers;

class ArrayHelperTest extends \CIUnitTestCase
{
	protected function setUp()
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
}
