<?php namespace CodeIgniter;

use CodeIgniter\Entity;

class EntityTest extends \CIUnitTestCase
{
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



	protected function getEntity()
	{
		return new class extends Entity
		{
			protected $foo;
			protected $bar;
			protected $default = 'sumfin';

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
