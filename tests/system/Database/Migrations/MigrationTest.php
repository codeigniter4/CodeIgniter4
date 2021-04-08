<?php

namespace CodeIgniter\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class MigrationTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

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
