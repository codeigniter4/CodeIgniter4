<?php namespace CodeIgniter;

use CodeIgniter\Entity;
use CodeIgniter\I18n\Time;
use CodeIgniter\Test\ReflectionHelper;

class EntityTest extends \CIUnitTestCase
{
	use ReflectionHelper;

	public function testSimpleSetAndGet()
	{
	    $entity = $this->getEntity();

	    $entity->foo = 'to wong';

	    $this->assertEquals('to wong', $entity->foo);
	}

	public function testGetterSetters()
	{
	    $entity = $this->getEntity();

	    $entity->bar = 'thanks';

	    $this->assertEquals('bar:thanks:bar', $entity->bar);
	}

	public function testUnsetResetsToDefaultValue()
	{
	    $entity = $this->getEntity();

	    $this->assertEquals('sumfin', $entity->default);

	    $entity->default = 'else';

		$this->assertEquals('else', $entity->default);

		unset($entity->default);

		$this->assertEquals('sumfin', $entity->default);
	}

	public function testIssetWorksLikeTraditionalIsset()
	{
	    $entity = $this->getEntity();

	    $this->assertTrue(isset($entity->default));
	    $this->assertFalse(isset($entity->foo));
	}

	public function testFill()
	{
	    $entity = $this->getEntity();

	    $entity->fill([
		    'foo' => 123,
		    'bar' => 234,
		    'baz' => 4556
	    ]);

	    $this->assertEquals(123, $entity->foo);
	    $this->assertEquals('bar:234:bar', $entity->bar);
	    $this->assertTrue(! isset($entity->baz));
	}

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

		$this->assertTrue(isset($entity->foo));
		$this->assertFalse(isset($entity->bar));
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

	public function testDateMutationFromString()
	{
		$entity = $this->getEntity();
		$this->setPrivateProperty($entity, 'created_at', '2017-07-15 13:23:34');

		$time = $entity->created_at;

		$this->assertTrue($time instanceof Time);
		$this->assertEquals('2017-07-15 13:23:34', $time->format('Y-m-d H:i:s'));
	}

	public function testDateMutationFromTimestamp()
	{
		$stamp = time();

		$entity = $this->getEntity();
		$this->setPrivateProperty($entity, 'created_at', $stamp);

		$time = $entity->created_at;

		$this->assertTrue($time instanceof Time);
		$this->assertEquals(date('Y-m-d H:i:s', $stamp), $time->format('Y-m-d H:i:s'));
	}

	public function testDateMutationFromDatetime()
	{
		$dt = new \DateTime('now');
		$entity = $this->getEntity();
		$this->setPrivateProperty($entity, 'created_at', $dt);

		$time = $entity->created_at;

		$this->assertTrue($time instanceof Time);
		$this->assertEquals($dt->format('Y-m-d H:i:s'), $time->format('Y-m-d H:i:s'));
	}

	public function testDateMutationFromTime()
	{
		$dt = Time::now();
		$entity = $this->getEntity();
		$this->setPrivateProperty($entity, 'created_at', $dt);

		$time = $entity->created_at;

		$this->assertTrue($time instanceof Time);
		$this->assertEquals($dt->format('Y-m-d H:i:s'), $time->format('Y-m-d H:i:s'));
	}

	public function testDateMutationStringToTime()
	{
		$entity = $this->getEntity();

		$entity->created_at = '2017-07-15 13:23:34';

		$time = $this->getPrivateProperty($entity, 'created_at');

		$this->assertTrue($time instanceof Time);
		$this->assertEquals('2017-07-15 13:23:34', $time->format('Y-m-d H:i:s'));
	}

	public function testDateMutationTimestampToTime()
	{
		$stamp = time();
		$entity = $this->getEntity();

		$entity->created_at = $stamp;

		$time = $this->getPrivateProperty($entity, 'created_at');

		$this->assertTrue($time instanceof Time);
		$this->assertEquals(date('Y-m-d H:i:s'), $time->format('Y-m-d H:i:s'));
	}

	public function testDateMutationDatetimeToTime()
	{
		$dt = new \DateTime('now');
		$entity = $this->getEntity();

		$entity->created_at = $dt;

		$time = $this->getPrivateProperty($entity, 'created_at');

		$this->assertTrue($time instanceof Time);
		$this->assertEquals($dt->format('Y-m-d H:i:s'), $time->format('Y-m-d H:i:s'));
	}

	public function testDateMutationTimeToTime()
	{
		$dt = Time::now();
		$entity = $this->getEntity();

		$entity->created_at = $dt;

		$time = $this->getPrivateProperty($entity, 'created_at');

		$this->assertTrue($time instanceof Time);
		$this->assertEquals($dt->format('Y-m-d H:i:s'), $time->format('Y-m-d H:i:s'));
	}

	public function testCastInteger()
	{
		$entity = $this->getCastEntity();

		$entity->first = 3.1;
		$this->assertTrue(is_integer($entity->first));
		$this->assertEquals(3, $entity->first);

		$entity->first = 3.6;
		$this->assertEquals(3, $entity->first);
	}

	public function testCastFloat()
	{
		$entity = $this->getCastEntity();

		$entity->second = 3;
		$this->assertTrue(is_float($entity->second));
		$this->assertEquals(3.0, $entity->second);

		$entity->second = '3.6';
		$this->assertTrue(is_float($entity->second));
		$this->assertEquals(3.6, $entity->second);
	}

	public function testCastDouble()
	{
		$entity = $this->getCastEntity();

		$entity->third = 3;
		$this->assertTrue(is_double($entity->third));
		$this->assertSame(3.0, $entity->third);

		$entity->third = '3.6';
		$this->assertTrue(is_double($entity->third));
		$this->assertSame(3.6, $entity->third);
	}

	public function testCastString()
	{
		$entity = $this->getCastEntity();

		$entity->fourth = 3.1415;
		$this->assertTrue(is_string($entity->fourth));
		$this->assertSame('3.1415', $entity->fourth);
	}

	public function testCastBoolean()
	{
		$entity = $this->getCastEntity();

		$entity->fifth = 1;
		$this->assertTrue(is_bool($entity->fifth));
		$this->assertSame(true, $entity->fifth);

		$entity->fifth = 0;
		$this->assertTrue(is_bool($entity->fifth));
		$this->assertSame(false, $entity->fifth);
	}

	public function testCastObject()
	{
		$entity = $this->getCastEntity();

		$data = ['foo' => 'bar'];

		$entity->sixth = $data;
		$this->assertTrue(is_object($entity->sixth));
		$this->assertEquals((object)$data, $entity->sixth);
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
		$this->assertTrue(is_integer($entity->ninth));
		$this->assertEquals(strtotime($date), $entity->ninth);
	}

	public function testCastArray()
	{
		$entity = $this->getCastEntity();

		$entity->seventh = ['foo' => 'bar'];

		// Should be a serialized string now...
		$check = $this->getPrivateProperty($entity, 'seventh');
		$this->assertEquals(serialize(['foo' => 'bar']), $check);

		$this->assertEquals(['foo' => 'bar'], $entity->seventh);
	}


	protected function getEntity()
	{
		return new class extends Entity
		{
			protected $foo;
			protected $bar;
			protected $default = 'sumfin';
			protected $created_at;

			public function setBar($value)
			{
			    $this->bar = "bar:{$value}";

			    return $this;
			}

			public function getBar()
			{
			    return "{$this->bar}:bar";
			}

		};
	}

	protected function getMappedEntity()
	{
		return new class extends Entity
		{
			protected $foo;
			protected $simple;

			// 'bar' is db column, 'foo' is internal representation
			protected $_options = [
				'dates' => [],
				'casts' => [],
				'datamap' => [
					'bar' => 'foo',
					'orig' => 'simple'
				]
			];

			protected function setSimple(string $val)
			{
				$this->simple = 'oo:'.$val;
			}

			protected function getSimple()
			{
				return $this->simple.':oo';
			}
		};
	}

	protected function getCastEntity()
	{
		return new class extends Entity
		{
			protected $first;
			protected $second;
			protected $third;
			protected $fourth;
			protected $fifth;
			protected $sixth;
			protected $seventh;
			protected $eighth;
			protected $ninth;

			// 'bar' is db column, 'foo' is internal representation
			protected $_options = [
				'casts' => [
					'first' => 'integer',
					'second' => 'float',
					'third' => 'double',
					'fourth' => 'string',
					'fifth' => 'boolean',
					'sixth' => 'object',
					'seventh' => 'array',
					'eighth' => 'datetime',
					'ninth' => 'timestamp'
				],
				'dates' => [],
				'datamap' => []
			];
		};
	}
}
