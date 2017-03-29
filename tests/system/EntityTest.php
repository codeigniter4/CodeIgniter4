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
}
