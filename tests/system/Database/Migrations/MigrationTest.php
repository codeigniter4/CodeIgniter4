<?php namespace CodeIgniter\Database;

use CodeIgniter\Test\CIDatabaseTestCase;

class MigrationTest extends CIDatabaseTestCase
{
	public function setUp(): void
	{
		parent::setUp();
	}

	public function testDBGroup()
	{
		$migration = new class extends Migration {
			protected $DBGroup = 'tests';
			function up()
			{
			}
			function down()
			{
			}
		};

		$dbGroup = $migration->getDBGroup();

		$this->assertEquals('tests', $dbGroup);
	}
}
