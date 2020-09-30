<?php

namespace CodeIgniter;

use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Entities\SupportEntity;

class EntityFactoryTest extends CIUnitTestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		EntityFactory::reset();
	}

	public function testCreateSingleInstance()
	{
		$bareEntity = EntityFactory::get('SupportEntity', [], false);
		$namespaced = EntityFactory::get('Tests\Support\Entities\SupportEntity', [], false);

		$this->assertInstanceOf('Tests\Support\Entities\SupportEntity', $bareEntity);
		$this->assertInstanceOf('Tests\Support\Entities\SupportEntity', $namespaced);
	}

	public function testInvalidEntityInstance()
	{
		$this->assertNull(EntityFactory::get('foo'));
		$this->assertNull(EntityFactory::get('Tests\Support\Entities\Foo'));
	}

	public function testCreateSharedInstances()
	{
		$this->assertSame(
			EntityFactory::get('SupportEntity'),
			EntityFactory::get('Tests\Support\Entities\SupportEntity')
		);
	}

	public function testInjectMockGivesEntityInstance()
	{
		EntityFactory::injectMock('Support', new SupportEntity());
		$this->assertInstanceOf('Tests\Support\Entities\SupportEntity', EntityFactory::get('Support'));
	}

	public function testResetInstancesGivesDifferentInstances()
	{
		$entity = EntityFactory::get('SupportEntity');
		EntityFactory::reset();
		$another = EntityFactory::get('SupportEntity');

		$this->assertNotSame($entity, $another);
	}

	public function testProceduralEntityInstanceGrabbing()
	{
		$one = entity('SupportEntity');
		$two = entity('Tests\Support\Entities\SupportEntity');

		$this->assertInstanceOf('Tests\Support\Entities\SupportEntity', $one);
		$this->assertInstanceOf('Tests\Support\Entities\SupportEntity', $two);
		$this->assertSame($one, $two);
	}
}
