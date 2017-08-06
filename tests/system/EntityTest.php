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
			protected $datamap = [
				'bar' => 'foo',
				'orig' => 'simple'
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
}
