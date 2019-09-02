<?php

namespace CodeIgniter;

use CodeIgniter\Exceptions\CastException;
use CodeIgniter\I18n\Time;
use CodeIgniter\Test\ReflectionHelper;
use Tests\Support\SomeEntity;

class EntityTest extends \CIUnitTestCase
{

	use ReflectionHelper;

	public function testSimpleSetAndGet()
	{
		$entity = $this->getEntity();

		$entity->foo = 'to wong';

		$this->assertEquals('to wong', $entity->foo);
	}

	//--------------------------------------------------------------------

	public function testGetterSetters()
	{
		$entity = $this->getEntity();

		$entity->bar = 'thanks';

		$this->assertEquals('bar:thanks:bar', $entity->bar);
	}

	public function testUnsetUnsetsAttribute()
	{
		$entity = $this->getEntity();

		$this->assertEquals('sumfin', $entity->default);

		$entity->default = 'else';

		$this->assertEquals('else', $entity->default);

		unset($entity->default);

		$this->assertNull($entity->default);
	}

	public function testIssetWorksLikeTraditionalIsset()
	{
		$entity = $this->getEntity();

		$this->assertFalse(isset($entity->foo));

		$attributes = $this->getPrivateProperty($entity, 'attributes');
		$this->assertFalse(isset($attributes['foo']));
		$this->assertTrue(isset($attributes['default']));
	}

	//--------------------------------------------------------------------

	public function testFill()
	{
		$entity = $this->getEntity();

		$entity->fill([
			'foo' => 123,
			'bar' => 234,
			'baz' => 4556,
		]);

		$this->assertEquals(123, $entity->foo);
		$this->assertEquals('bar:234:bar', $entity->bar);
		$this->assertObjectNotHasAttribute('baz', $entity);
	}

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1567
	 */
	public function testFillMapsEntities()
	{
		$entity = $this->getMappedEntity();

		$data = [
			'bar'  => 'foo',
			'orig' => 'simple',
		];
		$entity->fill($data);

		$this->assertEquals('foo', $entity->bar);
		$this->assertEquals('oo:simple:oo', $entity->orig);
	}

	//--------------------------------------------------------------------

	public function testDataMappingConvertsOriginalName()
	{
		$entity = $this->getMappedEntity();

		$entity->bar = 'made it';

		// Check mapped field
		$this->assertEquals('made it', $entity->foo);

		// Should also get from original name
		// since Model's would be looking for the original name
		$this->assertEquals('made it', $entity->bar);

		// But it shouldn't actually set a class property for the original name...
		$this->expectException(\ReflectionException::class);
		$this->getPrivateProperty($entity, 'bar');
	}

	public function testDataMappingWorksWithCustomSettersAndGetters()
	{
		$entity = $this->getMappedEntity();

		// Will map to "simple"
		$entity->orig = 'first';

		$this->assertEquals('oo:first:oo', $entity->simple);

		$entity->simple = 'second';

		$this->assertEquals('oo:second:oo', $entity->simple);
	}

	public function testIssetWorksWithMapping()
	{
		$entity = $this->getMappedEntity();

		// maps to 'foo'
		$entity->bar = 'here';

		$attributes = $this->getPrivateProperty($entity, 'attributes');

		$this->assertTrue(array_key_exists('foo', $attributes));
		$this->assertFalse(array_key_exists('bar', $attributes));
	}

	public function testUnsetWorksWithMapping()
	{
		$entity = $this->getMappedEntity();

		// maps to 'foo'
		$entity->bar = 'here';

		// doesn't work on original name
		unset($entity->bar);
		$this->assertEquals('here', $entity->bar);
		$this->assertEquals('here', $entity->foo);

		// does work on mapped field
		unset($entity->foo);
		$this->assertNull($entity->foo);
		$this->assertNull($entity->bar);
	}

	//--------------------------------------------------------------------

	public function testDateMutationFromString()
	{
		$entity     = $this->getEntity();
		$attributes = [
			'created_at' => '2017-07-15 13:23:34',
		];
		$this->setPrivateProperty($entity, 'attributes', $attributes);

		$time = $entity->created_at;

		$this->assertInstanceOf(Time::class, $time);
		$this->assertEquals('2017-07-15 13:23:34', $time->format('Y-m-d H:i:s'));
	}

	public function testDateMutationFromTimestamp()
	{
		$stamp = time();

		$entity     = $this->getEntity();
		$attributes = [
			'created_at' => $stamp,
		];
		$this->setPrivateProperty($entity, 'attributes', $attributes);

		$time = $entity->created_at;

		$this->assertInstanceOf(Time::class, $time);
		$this->assertCloseEnoughString(date('Y-m-d H:i:s', $stamp), $time->format('Y-m-d H:i:s'));
	}

	public function testDateMutationFromDatetime()
	{
		$dt         = new \DateTime('now');
		$entity     = $this->getEntity();
		$attributes = [
			'created_at' => $dt,
		];
		$this->setPrivateProperty($entity, 'attributes', $attributes);

		$time = $entity->created_at;

		$this->assertInstanceOf(Time::class, $time);
		$this->assertCloseEnoughString($dt->format('Y-m-d H:i:s'), $time->format('Y-m-d H:i:s'));
	}

	public function testDateMutationFromTime()
	{
		$dt         = Time::now();
		$entity     = $this->getEntity();
		$attributes = [
			'created_at' => $dt,
		];
		$this->setPrivateProperty($entity, 'attributes', $attributes);

		$time = $entity->created_at;

		$this->assertInstanceOf(Time::class, $time);
		$this->assertCloseEnoughString($dt->format('Y-m-d H:i:s'), $time->format('Y-m-d H:i:s'));
	}

	public function testDateMutationStringToTime()
	{
		$entity = $this->getEntity();

		$entity->created_at = '2017-07-15 13:23:34';

		$time = $this->getPrivateProperty($entity, 'attributes')['created_at'];

		$this->assertInstanceOf(Time::class, $time);
		$this->assertEquals('2017-07-15 13:23:34', $time->format('Y-m-d H:i:s'));
	}

	public function testDateMutationTimestampToTime()
	{
		$stamp  = time();
		$entity = $this->getEntity();

		$entity->created_at = $stamp;

		$time = $this->getPrivateProperty($entity, 'attributes')['created_at'];

		$this->assertInstanceOf(Time::class, $time);
		$this->assertCloseEnoughString(date('Y-m-d H:i:s'), $time->format('Y-m-d H:i:s'));
	}

	public function testDateMutationDatetimeToTime()
	{
		$dt     = new \DateTime('now');
		$entity = $this->getEntity();

		$entity->created_at = $dt;

		$time = $this->getPrivateProperty($entity, 'attributes')['created_at'];

		$this->assertInstanceOf(Time::class, $time);
		$this->assertCloseEnoughString($dt->format('Y-m-d H:i:s'), $time->format('Y-m-d H:i:s'));
	}

	public function testDateMutationTimeToTime()
	{
		$dt     = Time::now();
		$entity = $this->getEntity();

		$entity->created_at = $dt;

		$time = $this->getPrivateProperty($entity, 'attributes')['created_at'];

		$this->assertInstanceOf(Time::class, $time);
		$this->assertCloseEnoughString($dt->format('Y-m-d H:i:s'), $time->format('Y-m-d H:i:s'));
	}

	//--------------------------------------------------------------------

	public function testCastInteger()
	{
		$entity = $this->getCastEntity();

		$entity->first = 3.1;
		$this->assertInternalType('integer', $entity->first);
		$this->assertEquals(3, $entity->first);

		$entity->first = 3.6;
		$this->assertEquals(3, $entity->first);
	}

	public function testCastFloat()
	{
		$entity = $this->getCastEntity();

		$entity->second = 3;
		$this->assertInternalType('float', $entity->second);
		$this->assertEquals(3.0, $entity->second);

		$entity->second = '3.6';
		$this->assertInternalType('float', $entity->second);
		$this->assertEquals(3.6, $entity->second);
	}

	public function testCastDouble()
	{
		$entity = $this->getCastEntity();

		$entity->third = 3;
		$this->assertInternalType('double', $entity->third);
		$this->assertSame(3.0, $entity->third);

		$entity->third = '3.6';
		$this->assertInternalType('double', $entity->third);
		$this->assertSame(3.6, $entity->third);
	}

	public function testCastString()
	{
		$entity = $this->getCastEntity();

		$entity->fourth = 3.1415;
		$this->assertInternalType('string', $entity->fourth);
		$this->assertSame('3.1415', $entity->fourth);
	}

	public function testCastBoolean()
	{
		$entity = $this->getCastEntity();

		$entity->fifth = 1;
		$this->assertInternalType('bool', $entity->fifth);
		$this->assertTrue($entity->fifth);

		$entity->fifth = 0;
		$this->assertInternalType('bool', $entity->fifth);
		$this->assertFalse($entity->fifth);
	}

	public function testCastObject()
	{
		$entity = $this->getCastEntity();

		$data = ['foo' => 'bar'];

		$entity->sixth = $data;
		$this->assertInternalType('object', $entity->sixth);
		$this->assertEquals((object) $data, $entity->sixth);
	}

	public function testCastDateTime()
	{
		$entity = $this->getCastEntity();

		$entity->eighth = 'March 12, 2017';
		$this->assertInstanceOf('DateTime', $entity->eighth);
		$this->assertEquals('2017-03-12', $entity->eighth->format('Y-m-d'));
	}

	public function testCastTimestamp()
	{
		$entity = $this->getCastEntity();

		$date = 'March 12, 2017';

		$entity->ninth = $date;
		$this->assertInternalType('integer', $entity->ninth);
		$this->assertEquals(strtotime($date), $entity->ninth);
	}

	//--------------------------------------------------------------------

	public function testCastArray()
	{
		$entity = $this->getCastEntity();

		$entity->setSeventh(['foo' => 'bar']);

		$check = $this->getPrivateProperty($entity, 'attributes')['seventh'];
		$this->assertEquals(['foo' => 'bar'], $check);

		$this->assertEquals(['foo' => 'bar'], $entity->seventh);
	}

	public function testCastArrayByStringSerialize()
	{
		$entity = $this->getCastEntity();

		$entity->seventh = 'foobar';

		// Should be a serialized string now...
		$check = $this->getPrivateProperty($entity, 'attributes')['seventh'];
		$this->assertEquals(serialize('foobar'), $check);

		$this->assertEquals(['foobar'], $entity->seventh);
	}

	public function testCastArrayByArraySerialize()
	{
		$entity = $this->getCastEntity();

		$entity->seventh = ['foo' => 'bar'];

		// Should be a serialized string now...
		$check = $this->getPrivateProperty($entity, 'attributes')['seventh'];
		$this->assertEquals(serialize(['foo' => 'bar']), $check);

		$this->assertEquals(['foo' => 'bar'], $entity->seventh);
	}

	//--------------------------------------------------------------------

	public function testCastNullable()
	{
		$entity = $this->getCastNullableEntity();

		$this->assertSame(null, $entity->string_null);
		$this->assertSame('', $entity->string_empty);
		$this->assertSame(null, $entity->integer_null);
		$this->assertSame(0, $entity->integer_0);
	}

	//--------------------------------------------------------------------

	public function testCastAsJSON()
	{
		$entity = $this->getCastEntity();

		$entity->tenth = ['foo' => 'bar'];

		// Should be a JSON-encoded string now...
		$check = $this->getPrivateProperty($entity, 'attributes')['tenth'];
		$this->assertEquals('{"foo":"bar"}', $check);

		$this->assertEquals((object) ['foo' => 'bar'], $entity->tenth);
	}

	public function testCastAsJSONArray()
	{
		$entity = $this->getCastEntity();

		$data             = [
			'Sun',
			'Mon',
			'Tue',
		];
		$entity->eleventh = $data;

		// Should be a JSON-encoded string now...
		$check = $this->getPrivateProperty($entity, 'attributes')['eleventh'];
		$this->assertEquals('["Sun","Mon","Tue"]', $check);

		$this->assertEquals($data, $entity->eleventh);
	}

	public function testCastAsJSONErrorDepth()
	{
		$entity = $this->getCastEntity();

		// Create array with depth 513 to get depth error
		$array   = [];
		$value   = 'test value';
		$keys    = rtrim(str_repeat('test.', 513), '.');
		$keys    = explode('.', $keys);
		$current = &$array;
		foreach ($keys as $key)
		{
			$current = &$current[$key];
		}
		$current = $value;

		$this->expectException(CastException::class);
		$this->expectExceptionMessage('Maximum stack depth exceeded');

		$entity->tenth = $array;
		$this->getPrivateProperty($entity, 'tenth');
	}

	public function testCastAsJSONErrorUTF8()
	{
		$entity = $this->getCastEntity();

		$this->expectException(CastException::class);
		$this->expectExceptionMessage('Malformed UTF-8 characters, possibly incorrectly encoded');

		$entity->tenth = "\xB1\x31";
		$this->getPrivateProperty($entity, 'tenth');
	}

	public function testCastAsJSONSyntaxError()
	{
		$entity = new Entity();

		$method = $this->getPrivateMethodInvoker($entity, 'castAsJson');

		$this->expectException(CastException::class);
		$this->expectExceptionMessage('Syntax error, malformed JSON');

		$method('{ this is bad string', true);
	}

	public function testCastAsJSONAnotherErrorDepth()
	{
		$entity = new Entity();

		$method = $this->getPrivateMethodInvoker($entity, 'castAsJson');

		$this->expectException(CastException::class);
		$this->expectExceptionMessage('Maximum stack depth exceeded');

		$string = '{' . str_repeat('"test":{', 513) . '"test":"value"' . str_repeat('}', 513) . '}';

		$method($string, true);
	}

	public function testCastAsJSONControlCharCheck()
	{
		$entity = new Entity();

		$method = $this->getPrivateMethodInvoker($entity, 'castAsJson');

		$this->expectException(CastException::class);
		$this->expectExceptionMessage('Unexpected control character found');

		$string = "{\n\t\"property1\": \"The quick brown fox\njumps over the lazy dog\",\n\t\"property2\":\"value2\"\n}";

		$method($string, true);
	}

	public function testCastAsJSONStateMismatch()
	{
		$entity = new Entity();

		$method = $this->getPrivateMethodInvoker($entity, 'castAsJson');

		$this->expectException(CastException::class);
		$this->expectExceptionMessage('Underflow or the modes mismatch');

		$string = '[{"name":"jack","product_id":"1234"]';

		$method($string, true);
	}
	//--------------------------------------------------------------------

	public function testAsArray()
	{
		$entity = $this->getEntity();

		$result = $entity->toArray();

		$this->assertEquals($result, [
			'foo'        => null,
			'bar'        => ':bar',
			'default'    => 'sumfin',
			'created_at' => null,
			'createdAt'  => null,
		]);
	}

	public function testAsArrayMapped()
	{
		$entity = $this->getMappedEntity();

		$result = $entity->toArray();

		$this->assertEquals($result, [
			'foo'    => null,
			'simple' => ':oo',
			'bar'    => null,
			'orig'   => ':oo',
		]);
	}

	public function testAsArrayOnlyChanged()
	{
		$entity = $this->getEntity();

		$entity->bar = 'foo';

		$result = $entity->toArray(true);

		$this->assertEquals($result, [
			'bar' => 'bar:foo:bar',
		]);
	}

	public function testToRawArray()
	{
		$entity = $this->getEntity();

		$result = $entity->toRawArray();

		$this->assertEquals($result, [
			'foo'        => null,
			'bar'        => null,
			'default'    => 'sumfin',
			'created_at' => null,
		]);
	}

	public function testToRawArrayOnlyChanged()
	{
		$entity = $this->getEntity();

		$entity->bar = 'foo';

		$result = $entity->toRawArray(true);

		$this->assertEquals($result, [
			'bar' => 'bar:foo',
		]);
	}

	//--------------------------------------------------------------------

	public function testFilledConstruction()
	{
		$data = [
			'foo' => 'bar',
			'bar' => 'baz',
		];

		$something = new SomeEntity($data);
		$this->assertEquals('bar', $something->foo);
		$this->assertEquals('baz', $something->bar);
	}

	//--------------------------------------------------------------------

	public function testChangedArray()
	{
		$data = [
			'bar' => 'baz',
		];

		$something = new SomeEntity($data);
		$whatsnew  = $something->toArray(true);
		$expected  = $data;
		$this->assertEquals($expected, $whatsnew);

		$something->magic  = 'rockin';
		$expected['magic'] = 'rockin';
		$expected['foo']   = null;
		$whatsnew          = $something->toArray(false);
		$this->assertEquals($expected, $whatsnew);
	}

	//--------------------------------------------------------------------

	public function testHasChangedNotExists()
	{
		$entity = new SomeEntity();

		$this->assertFalse($entity->hasChanged('foo'));
	}

	public function testHasChangedNewElement()
	{
		$entity = new SomeEntity();

		$entity->foo = 'bar';

		$this->assertTrue($entity->hasChanged('foo'));
	}

	public function testHasChangedNoChange()
	{
		$entity = $this->getEntity();

		$this->assertFalse($entity->hasChanged('default'));
	}

	public function testHasChangedWholeEntity()
	{
		$entity = $this->getEntity();

		$entity->foo = 'bar';

		$this->assertTrue($entity->hasChanged());
	}

	public function testIssetKeyMap()
	{
		$entity = $this->getEntity();

		$entity->created_at = '12345678';
		$this->assertTrue(isset($entity->createdAt));

		$entity->bar = 'foo';
		$this->assertTrue(isset($entity->FakeBar));
	}

	protected function getEntity()
	{
		return new class extends Entity
		{
			protected $attributes = [
				'foo'        => null,
				'bar'        => null,
				'default'    => 'sumfin',
				'created_at' => null,
			];

			protected $original = [
				'foo'        => null,
				'bar'        => null,
				'default'    => 'sumfin',
				'created_at' => null,
			];

			protected $datamap = [
				'createdAt' => 'created_at',
			];

			public function setBar($value)
			{
				$this->attributes['bar'] = "bar:{$value}";

				return $this;
			}

			public function getBar()
			{
				return "{$this->attributes['bar']}:bar";
			}

			public function getFakeBar()
			{
				return "{$this->attributes['bar']}:bar";
			}
		};
	}

	protected function getMappedEntity()
	{
		return new class extends Entity
		{
			protected $attributes = [
				'foo'    => null,
				'simple' => null,
			];

			protected $_original = [
				'foo'    => null,
				'simple' => null,
			];

			// 'bar' is db column, 'foo' is internal representation
			protected $datamap = [
				'bar'  => 'foo',
				'orig' => 'simple',
			];

			protected function setSimple(string $val)
			{
				$this->attributes['simple'] = 'oo:' . $val;
			}

			protected function getSimple()
			{
				return $this->attributes['simple'] . ':oo';
			}
		};
	}

	protected function getCastEntity()
	{
		return new class extends Entity
		{
			protected $attributes = [
				'first'    => null,
				'second'   => null,
				'third'    => null,
				'fourth'   => null,
				'fifth'    => null,
				'sixth'    => null,
				'seventh'  => null,
				'eighth'   => null,
				'ninth'    => null,
				'tenth'    => null,
				'eleventh' => null,
			];

			protected $_original = [
				'first'    => null,
				'second'   => null,
				'third'    => null,
				'fourth'   => null,
				'fifth'    => null,
				'sixth'    => null,
				'seventh'  => null,
				'eighth'   => null,
				'ninth'    => null,
				'tenth'    => null,
				'eleventh' => null,
			];

			// 'bar' is db column, 'foo' is internal representation
			protected $casts = [
				'first'    => 'integer',
				'second'   => 'float',
				'third'    => 'double',
				'fourth'   => 'string',
				'fifth'    => 'boolean',
				'sixth'    => 'object',
				'seventh'  => 'array',
				'eighth'   => 'datetime',
				'ninth'    => 'timestamp',
				'tenth'    => 'json',
				'eleventh' => 'json-array',
			];

			public function setSeventh($seventh)
			{
				$this->attributes['seventh'] = $seventh;
			}
		};
	}

	protected function getCastNullableEntity()
	{
		return new class extends Entity
		{
			protected $attributes = [
				'string_null'  => null,
				'string_empty' => null,
				'integer_null' => null,
				'integer_0'    => null,
			];
			protected $_original  = [
				'string_null'  => null,
				'string_empty' => null,
				'integer_null' => null,
				'integer_0'    => null,
			];

			// 'bar' is db column, 'foo' is internal representation
			protected $casts = [
				'string_null'  => '?string',
				'string_empty' => 'string',
				'integer_null' => '?integer',
				'integer_0'    => 'integer',
			];
		};
	}

}
