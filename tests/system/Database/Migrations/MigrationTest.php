<?php

namespace CodeIgniter\Database;

use CodeIgniter\Test\CIDatabaseTestCase;

class MigrationTest extends CIDatabaseTestCase
{
	protected function setUp(): void
	{
		parent::setUp();
	}

	public function testDBGroup()
	{
		$migration          = new class() extends Migration {
			protected $DBGroup = 'tests';

			public function up()
			{
			}

			public function down()
			{
			}
		};

		$dbGroup = $migration->getDBGroup();

		$this->assertEquals('tests', $dbGroup);
	}
}
