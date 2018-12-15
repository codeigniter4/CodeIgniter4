<?php namespace CodeIgniter\Database\Live;

;

use CodeIgniter\Test\CIDatabaseTestCase;
use Config\Database;

class ConnectTest extends CIDatabaseTestCase
{
	protected $group1;

	protected $group2;

	protected function setUp()
	{
		parent::setUp();

		$config = config('Database');

		$this->group1 = $config->default;
		$this->group2 = $config->default;

		$this->group1['strictOn'] = false;
		$this->group2['strictOn'] = true;
	}

	public function testConnectWithMultipleCustomGroups()
	{
		// We should have our test database connection already.
		$instances = $this->getPrivateProperty(Database::class, 'instances');
		$this->assertEquals(1, count($instances));

		$db1 = Database::connect($this->group1);
		$db2 = Database::connect($this->group2);

		$this->assertNotSame($db1, $db2);

		$instances = $this->getPrivateProperty(Database::class, 'instances');
		$this->assertEquals(3, count($instances));
	}

	//--------------------------------------------------------------------

}
